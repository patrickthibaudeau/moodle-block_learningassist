<?php

namespace block_learningassist;




use core\di;
use core\exception\coding_exception;
use core_ai\manager;
use Exception;

abstract class gen_ai
{

    /**
     * This function uses the built-in Moodle AI providers and placements
     * @param $prompt string
     * @param string $lang
     * @return string|null
     * @throws Exception
     */
    public static function make_call(string $prompt, array $history, string $lang = 'en'): ?string
    {
        global $CFG;

        // Always return the response in the language of the course
        $prompt .= "\n\nYou must return the response in the language based on this language code: $lang.\n\n";

        $messages = array(
            ...$history,
            array(
                'role' => 'user',
                'content' => $prompt
            ),

        );

        // Get AI manager.
        $manager = di::get(manager::class);

        // Get provider instances
        $provider_instances = $manager->get_provider_instances(['provider' => 'aiprovider_azureai\\provider']);

        // Get first item in the array
        $provider_instance = reset($provider_instances);

        if (empty($provider_instance)) {
            throw new Exception('No provider instances found');
        }

        // log to file
        $provider_config = self::get_text_provider($provider_instance);
        $response = self::azure_openai_chat(
            $messages,
            $provider_config->apikey,
            $provider_config->endpoint,
            $provider_config->deployment,
            $provider_config->apiversion
        );

        return markdown_to_html($response);

    }

    /**
     * This function returns the Azure OpenAI provider and all paramaters
     * @param $provider_instance \core_ai\provider_instance
     * @return \stdClass
     */
    private static function get_text_provider($provider_instance): \stdClass
    {
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
    public static function azure_openai_chat($messages, $api_key, $endpoint, $deployment_id, $api_version): array|string
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
        return $content;

    }


    /**
     * This function creates an OpenAI chat session
     */
    public static function openai_chat($messages, $api_key, $model = 'gpt-3.5-turbo'): string
    {
        $url = 'https://api.openai.com/v1/chat/completions';

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer $api_key"
        ];

        $data = [
            "model" => $model,
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
        return $content;
    }



    /**
     * Get the cache instance for chat history.
     * @return \cache_application
     */
    private static function get_cache(): \cache_application
    {
        return \cache::make('block_learningassist', 'chat_history');
    }

    /**
     * Get chat history for a specific chatid.
     * @param string $chatid
     * @return array
     * @throws coding_exception
     */
    public static function get_history(string $chatid): array
    {
        $cache = self::get_cache();
        $key = self::normalize_cache_key($chatid);
        $history = $cache->get($key);
        return ($history === false || !is_array($history)) ? [] : $history;
    }


    private static function normalize_cache_key(string $key): string
    {
        return sha1($key);
    }

    /**
     * Set full history for a specific chatid.
     * @param string $chatid
     * @param array $history
     */
    public static function set_history(string $chatid, array $history): void
    {
        $cache = self::get_cache();
        $key = self::normalize_cache_key($chatid);
        $cache->set($key, $history);
    }

    /**
     * Clear the chat history for a specific chatid.
     * @param string $chatid
     */
    public static function clear_history(string $chatid): void
    {
        $cache = self::get_cache();
        $key = self::normalize_cache_key($chatid);
        $cache->delete($key);
    }


    /**
     * Add an entry to the chat history.
     * @param string $chatid
     * @param string $role
     * @param string $content
     * @throws coding_exception
     */
    public static function add_to_history(string $chatid, string $role, string $content): void
    {
        $history = self::get_history($chatid);
        $history[] = ['role' => $role, 'content' => $content];
        self::set_history($chatid, $history);
    }

}