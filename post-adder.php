<?php

/**
 * Plugin Name:       Post Line Adder
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       This plugin adds extra line on posts.
 * Version:           1.0
 * Author:            Giorgi Lomaia
 * Author URI:        https://author.example.com/
 */



class WordCountAndTimePlugin{
    function __construct(){
        add_action('admin_menu',array($this,'adminPage'));
        add_action('admin_init',array($this,'settings'));
        add_filter('the_content',array($this,'ifWrap'));
    }
    function ifWrap($content){
        if((is_main_query() AND is_single()) AND 
        (get_option('wcp_wordcount','1') OR 
        get_option('wcp_charactercount','1') OR 
        get_option('wcp_readtime','1'))){
            return $this->createHTML($content);
    }
    return $content;
}
    function createHTML($content){
      $html = '<h3>' . get_option('wcp_headline','Post Statistics') . '</h3><p>';
      // Get word count once between both word count and read time will need it.
      if(get_option('wcp_wordcount','1') OR get_option('wcp_readtime',1)){
        $wordCount = str_word_count(strip_tags($content));
      }
      if(get_option('wcp_wordcount','1') )
      {
        $html .= 'This post has ' . '<b>' . $wordCount . '</b>' . ' words.<br>';
      }
      if(get_option('wcp_charactercount','1') )
      {
        $html .= 'This post has ' . '<b>' . strlen(strip_tags($content)) . '</b>' . ' characters.<br>';
      }
      if(get_option('wcp_readtime','1') )
      {
        $html .= 'This post will take ' . '<b>' . round($wordCount/225) . '</b>' . ' minute(s) to read.<br>';
      }
      $html .= "</p>";
      if(get_option('wcp_location','0')==='0'){
        return $html . $content;
      }
      return $content . $html;
    }
    function settings()
    {   
        // Adds a new section to a settings page.
        add_settings_section('wcp_first_section',null,null,'word-count-settings-page');

        // Adds a new field to a section of a settings page
        add_settings_field('wcp_location','Display Location',array($this,'locationHTML'),
        'word-count-settings-page','wcp_first_section', array('theName' => 'wcp_location'));
        // Registers a setting and its data.
        register_setting('wordcountplugin','wcp_location',array('sanitize_callback'=>array($this,'sanitizeLocation'), 'default' => '0'));

        add_settings_field('wcp_headline','Headline Text',array($this,'headlineHTML'),'word-count-settings-page','wcp_first_section', array('theName' => 'wcp_headline'));
        register_setting('wordcountplugin','wcp_headline',array('sanitize_callback'=>'sanitize_text_field', 'default' => 'Post Statistics'));

        add_settings_field('wcp_wordcount','Word Count',array($this,'checkboxHTML'),'word-count-settings-page','wcp_first_section', array('theName' => 'wcp_wordcount'));
        register_setting('wordcountplugin','wcp_wordcount',array('sanitize_callback'=>'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_charactercount','Character Count',array($this,'checkboxHTML'),'word-count-settings-page','wcp_first_section', array('theName' => 'wcp_charactercount'));
        register_setting('wordcountplugin','wcp_charactercount',array('sanitize_callback'=>'sanitize_text_field', 'default' => '1'));

        add_settings_field('wcp_readtime','Read Time',array($this,'checkboxHTML'),'word-count-settings-page','wcp_first_section', array('theName' => 'wcp_readtime'));
        register_setting('wordcountplugin','wcp_readtime',array('sanitize_callback'=>'sanitize_text_field', 'default' => '1'));
        
    }
    function sanitizeLocation($input){
        if($input != '0' && $input != '1'){
            add_settings_error('wcp_location','wcp_location_error','Display Location must be either beginning or ending');
            return get_option('wcp_location');
        }
        return $input;
    }
    function checkboxHTML($args){?>
        <input type="checkbox" name="<?php echo $args["theName"]?>" value="1" <?php checked(get_option($args["theName"]),'1')?>>
    <?php
    }
    function locationHTML(){?>
        <select name="wcp_location">
            <option value="0" <?php selected(get_option('wcp_location'),'0'); ?>>Beginning of Post </option>
            <option value="1" <?php selected(get_option('wcp_location'),'1'); ?>>End of Post </option>
        </select>
    <?php
    }
    function headlineHTML(){?>
    <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline'));?>">
    <?php
    }
    function adminPage()
    {
        add_options_page('Word Counter Settings','Word Count','manage_options','word-count-settings-page',array($this,'ourHTML'));
    }
    function ourHTML(){?>
       <div class="wrap">
        <h1>Word Count Settings</h1>
        <form action="options.php" method="POST">
            <?php 
                // Displays hidden fields and handles security of our options form
                settings_fields('wordcountplugin');
                // Prints out all settings sections added to a particular settings page
                do_settings_sections('word-count-settings-page');
                submit_button();
            ?>
       </div>
    <?php
    }
}
$wordCountAndTimePlugin = new wordCountAndTimePlugin();


