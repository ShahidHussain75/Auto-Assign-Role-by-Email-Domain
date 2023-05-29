<?php
/*
Plugin Name: Chatbot Support Agent
Plugin URI: http://yourwebsite.com
Description: A WordPress plugin for integrating a chatbot support agent using the ChatGPT API.
Version: 1.0.0
Author: Your Name
Author URI: http://yourwebsite.com
License: GPL2
*/

// Enqueue necessary scripts and styles
function chatbot_support_agent_enqueue_scripts() {
    wp_enqueue_script('chatbot-support-agent-script', plugins_url('js/chatbot-support-agent.js', __FILE__), array('jquery'), '1.0', true);

    // Pass API key, instructions, and avatar image URL to the JavaScript file
    $api_key = get_option('chatbot-support-agent-api-key');
    $instructions = get_option('chatbot-support-agent-instructions');
    $avatar_url = plugins_url('images/avatar.png', __FILE__); // Replace 'images/avatar.png' with the actual path to your avatar image
    wp_localize_script('chatbot-support-agent-script', 'chatbotSupportAgentData', array(
        'api_key' => $api_key,
        'instructions' => $instructions,
        'avatar_url' => $avatar_url
    ));

    wp_enqueue_style('chatbot-support-agent-style', plugins_url('css/chatbot-support-agent.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'chatbot_support_agent_enqueue_scripts');

// Add the chatbot interface to the WordPress website
function chatbot_support_agent_add_interface() {
    echo '<div id="chatbot-support-agent"></div>';
}
add_action('wp_footer', 'chatbot_support_agent_add_interface');

// Rest of the code...
