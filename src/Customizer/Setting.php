<?php
/**
 * Customizer Setting
 * 
 * Represents a WordPress Customizer setting with control
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Customizer;

defined('ABSPATH') or exit;

class Setting {
    /**
     * Full setting ID (with section prefix)
     * 
     * @var string
     */
    private $fullId;
    
    /**
     * Setting ID (without prefix)
     * 
     * @var string
     */
    private $id;
    
    /**
     * Section instance
     * 
     * @var Section
     */
    private $section;
    
    /**
     * Control type
     * 
     * @var string
     */
    private $controlType = 'text';
    
    /**
     * Control label
     * 
     * @var string
     */
    private $label;
    
    /**
     * Control description
     * 
     * @var string
     */
    private $description = '';
    
    /**
     * Default value
     * 
     * @var mixed
     */
    private $default = '';
    
    /**
     * Setting transport method
     * 
     * @var string
     */
    private $transport = 'refresh';
    
    /**
     * Control choices (for select, radio, etc.)
     * 
     * @var array
     */
    private $choices = [];
    
    /**
     * Input attributes
     * 
     * @var array
     */
    private $inputAttrs = [];
    
    /**
     * Setting capability
     * 
     * @var string
     */
    private $capability = 'edit_theme_options';
    
    /**
     * Setting type
     * 
     * @var string
     */
    private $settingType = 'theme_mod';
    
    /**
     * Constructor
     * 
     * @param string $fullId Full setting ID
     * @param string $id Setting ID
     * @param Section $section
     */
    public function __construct($fullId, $id, Section $section) {
        $this->fullId = $fullId;
        $this->id = $id;
        $this->section = $section;
        $this->label = ucfirst(str_replace(['_', '-'], ' ', $id));
    }
    
    /**
     * Set control type
     * 
     * @param string $type Control type
     * @return self
     */
    public function control($type) {
        $this->controlType = $type;
        return $this;
    }
    
    /**
     * Set control label
     * 
     * @param string $label
     * @return self
     */
    public function label($label) {
        $this->label = $label;
        return $this;
    }
    
    /**
     * Set control description
     * 
     * @param string $description
     * @return self
     */
    public function description($description) {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Set default value
     * 
     * @param mixed $default
     * @return self
     */
    public function default($default) {
        $this->default = $default;
        return $this;
    }
    
    /**
     * Set transport method
     * 
     * @param string $transport 'refresh' or 'postMessage'
     * @return self
     */
    public function transport($transport) {
        $this->transport = $transport;
        return $this;
    }
    
    /**
     * Set choices for select/radio controls
     * 
     * @param array $choices
     * @return self
     */
    public function choices($choices) {
        $this->choices = $choices;
        return $this;
    }
    
    /**
     * Set input attributes
     * 
     * @param array $attrs
     * @return self
     */
    public function inputAttrs($attrs) {
        $this->inputAttrs = $attrs;
        return $this;
    }
    
    /**
     * Set setting capability
     * 
     * @param string $capability
     * @return self
     */
    public function capability($capability) {
        $this->capability = $capability;
        return $this;
    }
    
    /**
     * Set setting type ('theme_mod' or 'option')
     * 
     * @param string $type
     * @return self
     */
    public function type($type) {
        $this->settingType = $type;
        return $this;
    }
    
    /**
     * Register setting and control with WordPress Customizer
     * 
     * @param \WP_Customize_Manager $wp_customize
     * @param string $sectionId
     * @return void
     */
    public function register($wp_customize, $sectionId) {
        // Add setting
        $wp_customize->add_setting($this->fullId, [
            'default' => $this->default,
            'transport' => $this->transport,
            'sanitize_callback' => $this->getSanitizeCallback(),
            'capability' => $this->capability,
            'type' => $this->settingType,
        ]);
        
        // Add control
        $controlArgs = [
            'label' => $this->label,
            'description' => $this->description,
            'section' => $sectionId,
            'settings' => $this->fullId,
            'type' => $this->controlType,
        ];
        
        if (!empty($this->choices)) {
            $controlArgs['choices'] = $this->choices;
        }
        
        if (!empty($this->inputAttrs)) {
            $controlArgs['input_attrs'] = $this->inputAttrs;
        }
        
        // Use appropriate control class based on type
        switch ($this->controlType) {
            case 'media':
                $wp_customize->add_control(new \WP_Customize_Media_Control(
                    $wp_customize,
                    $this->fullId,
                    $controlArgs
                ));
                break;
                
            case 'color':
                $wp_customize->add_control(new \WP_Customize_Color_Control(
                    $wp_customize,
                    $this->fullId,
                    $controlArgs
                ));
                break;
                
            case 'image':
                $wp_customize->add_control(new \WP_Customize_Image_Control(
                    $wp_customize,
                    $this->fullId,
                    $controlArgs
                ));
                break;
                
            case 'upload':
                $wp_customize->add_control(new \WP_Customize_Upload_Control(
                    $wp_customize,
                    $this->fullId,
                    $controlArgs
                ));
                break;
                
            case 'radio':
            case 'select':
            case 'dropdown-pages':
            case 'checkbox':
            case 'text':
            case 'textarea':
            case 'email':
            case 'url':
            case 'number':
            case 'range':
            case 'date':
            case 'tel':
                $wp_customize->add_control($this->fullId, $controlArgs);
                break;
                
            default:
                $wp_customize->add_control($this->fullId, $controlArgs);
                break;
        }
    }
    
    /**
     * Get sanitize callback based on control type
     * 
     * @return string|callable
     */
    private function getSanitizeCallback() {
        switch ($this->controlType) {
            case 'email':
                return 'sanitize_email';
            case 'url':
                return 'esc_url_raw';
            case 'color':
                return 'sanitize_hex_color';
            case 'checkbox':
                return function($value) { return $value ? 1 : 0; };
            case 'number':
            case 'range':
                return 'absint';
            case 'textarea':
                return 'wp_kses_post';
            case 'radio':
            case 'select':
                return function($value) {
                    return array_key_exists($value, $this->choices) ? $value : '';
                };
            case 'dropdown-pages':
                return 'absint';
            case 'image':
            case 'upload':
            case 'media':
                return 'esc_url_raw';
            case 'tel':
                return 'sanitize_text_field';
            case 'date':
                return function($value) {
                    return date('Y-m-d', strtotime($value));
                };
            default:
                return 'sanitize_text_field';
        }
    }
    
    /**
     * Get setting value
     * 
     * @return mixed
     */
    public function getValue() {
        return get_theme_mod($this->fullId, $this->default);
    }
}