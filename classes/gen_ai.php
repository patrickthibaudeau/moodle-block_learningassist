<?php

namespace block_learningassist;

abstract class gen_ai
{
    /**
     * This function uses the built in Moodle AI providers and placements
     * @param $context \stdClass
     * @param $prompt string
     * @param bool $decode bool Wheter to JSON decode the response or not
     * @return mixed
     */
    public static function make_call($system_message, $prompt, $lang = 'en', $decode = false)
    {
        global $USER;
        // Always return the response in the language of the course
        $prompt .= "\n\nYou must return the response in the language based on this language code: $lang.\n\n";

        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_message
            ),
            array(
                'role' => 'user',
                'content' => $prompt
            ),
        );
        // Get AI manager.
        $manager = \core\di::get(\core_ai\manager::class);
        // Get provider instances
        $provider_instances = $manager->get_provider_instances();
        foreach ($provider_instances as $provider_instance) {
            // Check if the provider is enabled
            if ($provider_instance->enabled == true) {
                switch ($provider_instance->provider) {
                    case 'aiprovider_azureai':
                        $provider = self::get_azure_provider($provider_instance);
                        return self::azure_openai_chat($messages,
                            $provider->apikey,
                            $provider->endpoint,
                            $provider->deployment,
                            $provider->apiversion
                        );
                        break;
                }
            }
        }
    }

    /**
     * This function returns the Azure OpenAI provider and all paramaters
     * @param $provider_instance \core_ai\provider_instance
     * @return \stdClass
     */
    private static function get_azure_provider($provider_instance) {
        $provider = new \stdClass();
        $provider->apikey = $provider_instance->config['apikey'];
        $provider->endpoint = $provider_instance->config['endpoint'];
        foreach ($provider_instance->actionconfig as $key => $action_config) {
            if ($key == 'core_ai\aiactions\generate_text') {
                $provider->deployment = $provider_instance->actionconfig[$key]['settings']['deployment'];
                $provider->apiversion = $provider_instance->actionconfig[$key]['settings']['apiversion'];
            }
        }
        return $provider;
    }

    /**
     * This function creates an Azure OprenAI chat session
     */
    public static function azure_openai_chat($messages, $api_key, $endpoint, $deployment_id, $api_version)
    {
        $url = $endpoint . "/openai/deployments/$deployment_id/chat/completions?api-version=$api_version";
        $headers = [
            "Content-Type: application/json",
            "api-key: $api_key"
        ];
        $data = [
            "messages" => $messages,
            "max_tokens" => 4096,
            "temperature" => 0.7
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($result, true);
        $content = $response['choices'][0]['message']['content'] ?? '';
        $content = str_replace('```html', '', $content);
        return $content;
    }
}