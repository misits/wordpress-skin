<?php
/**
 * Customizer Section
 * 
 * Represents a WordPress Customizer section with settings and controls
 * 
 * @package WP-Skin
 */

namespace WordPressSkin\Customizer;

defined('ABSPATH') or exit;

class Section {
    /**
     * Section ID
     * 
     * @var string
     */
    private $id;
    
    /**
     * Section title
     * 
     * @var string
     */
    private $title;
    
    /**
     * Section description
     * 
     * @var string
     */
    private $description = '';
    
    /**
     * Section priority
     * 
     * @var int
     */
    private $priority = 160;
    
    /**
     * Settings and controls
     * 
     * @var array
     */
    private $settings = [];
    
    /**
     * Customizer manager instance
     * 
     * @var CustomizerManager
     */
    private $manager;
    
    /**
     * Constructor
     * 
     * @param string $id Section ID
     * @param CustomizerManager $manager
     */
    public function __construct($id, CustomizerManager $manager) {
        $this->id = $id;
        $this->manager = $manager;
        $this->title = ucfirst(str_replace(['_', '-'], ' ', $id));
    }
    
    /**
     * Set section title
     * 
     * @param string $title
     * @return self
     */
    public function title($title) {
        $this->title = $title;
        return $this;
    }
    
    /**
     * Set section description
     * 
     * @param string $description
     * @return self
     */
    public function description($description) {
        $this->description = $description;
        return $this;
    }
    
    /**
     * Set section priority
     * 
     * @param int $priority
     * @return self
     */
    public function priority($priority) {
        $this->priority = $priority;
        return $this;
    }
    
    /**
     * Add a setting with control
     * 
     * @param string $settingId Setting ID
     * @return Setting
     */
    public function setting($settingId) {
        $fullSettingId = $this->id . '_' . $settingId;
        
        if (!isset($this->settings[$settingId])) {
            $this->settings[$settingId] = new Setting($fullSettingId, $settingId, $this);
        }
        
        return $this->settings[$settingId];
    }
    
    /**
     * Register section with WordPress Customizer
     * 
     * @param \WP_Customize_Manager $wp_customize
     * @return void
     */
    public function register($wp_customize) {
        // Add section
        $wp_customize->add_section($this->id, [
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
        ]);
        
        // Add settings and controls
        foreach ($this->settings as $setting) {
            $setting->register($wp_customize, $this->id);
        }
    }
    
    /**
     * Get section ID
     * 
     * @return string
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Get all settings
     * 
     * @return array
     */
    public function getSettings() {
        return $this->settings;
    }
}