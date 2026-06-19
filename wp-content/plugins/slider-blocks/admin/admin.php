<?php
/**
 * Plugin Admin Page 
 * @package GutSliderBlocks
 */	

if (!defined('ABSPATH')) exit;

if (!class_exists('GutSlider_Admin')) {
    class GutSlider_Admin {
        /**
         * Slider block settings
         */
        private const BLOCK_SETTINGS = [
            'gut_fixed_content_slider',
            'gut_any_content_slider',
            'gut_testimonial_slider',
            'gut_post_slider',
            'gut_photo_carousel',
            'gut_logo_carousel',
            'gut_before_after_slider',
            'gut_videos_carousel',
            'gut_shader_slider',
            'gut_shutters_slider',
            'gut_slicer_slider',
            'gut_fashion_slider',
            'gut_triple_slider',
            'gut_spring_carousel',
            'gut_panorama_carousel',
            'gut_three_d_carousel',
            'gut_card_slider',
            'gut_marquee_carousel',
            'gut_material_carousel',
            'gut_tinder_slider',
            'gut_hover_slider',
            'gut_product_carousel',
            'gut_product_categories_carousel',
        ];

        /**
         * Constructor
         */
        public function __construct() {
            add_action('admin_menu', [$this, 'admin_menu'], 20);
            add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
            add_action('admin_init', [$this, 'initialize_admin']);
            add_action('rest_api_init', [$this, 'register_settings']);
            add_action('wp_ajax_gutslider_toggle_block', [$this, 'toggle_block_status']);
        }

        /**
         * Initialize admin functionality
         */
        public function initialize_admin() {
            $this->register_settings();
        }
        
        /**
         * Enqueue admin scripts and styles
         * @param string $screen Current admin screen
         */
        public function admin_assets($screen) {
            if ($screen !== 'toplevel_page_gutslider-blocks' && $screen !== 'gutslider_page_gutslider-blocks-settings' && $screen !== 'gutslider_page_gutslider-license') {
                return;
            }

            $this->enqueue_admin_assets();
        }
    
        /**
         * Add admin menu
         */
        public function admin_menu() {
            add_menu_page(
                __('GutSlider', 'slider-blocks'),
                __('GutSlider', 'slider-blocks'),
                'manage_options',
                'gutslider-blocks',
                [$this, 'render_admin_page'],
                'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iMTIiIGZpbGw9IiNEOUQ5RDkiLz4KPHBhdGggZD0iTTE1IDdIMTEuODIzNUg5VjE3SDE1VjEyLjQxNjdIMTIuODgyNEgxMS44MjM1IiBzdHJva2U9IiMxRDIzMjciIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+Cjwvc3ZnPgo=',
                100
            );

            add_submenu_page(
                'gutslider-blocks',
                __( 'Welcome', 'slider-blocks' ),
                __( 'Welcome', 'slider-blocks' ),
                'manage_options',
                'gutslider-blocks',
                array( $this, 'render_admin_page' )
            );

            add_submenu_page(
                'gutslider-blocks',
                __( 'Blocks', 'slider-blocks' ),
                __( 'Blocks', 'slider-blocks' ),
                'manage_options',
                'gutslider-blocks-settings',
                array( $this, 'render_blocks_page' )
            );

        }

        /**
         * Render main admin page
         */
        public function render_admin_page() {
                $has_pro = defined('GUTSLIDER_PRO_VERSION');
            ?>
            <div class="header bg-white box-shadow">
                <div class="container flex align-center">
                     <div class="header-content flex flex-1 align-center">
                        <div class="logo-area flex align-center gap-8">
                            <img src="<?php echo esc_url( GUTSLIDER_URL . 'admin/img/gutslider.svg' ); ?>" alt="<?php esc_attr_e('GutSlider Logo', 'slider-blocks'); ?>" class="logo-img"/>
                        </div>
                        <div class="nav flex align-center">
                            <a href="<?php echo esc_url( admin_url('admin.php?page=gutslider-blocks') ); ?>" class="nav-link"><?php esc_html_e('Welcome', 'slider-blocks'); ?></a>
                            <span class="dot"></span>
                            <a href="<?php echo esc_url( admin_url('admin.php?page=gutslider-blocks-settings') ); ?>" class="nav-link"><?php esc_html_e('Blocks', 'slider-blocks'); ?></a>
                            <span class="dot"></span>
                            <a href="<?php echo $has_pro ? esc_url( admin_url('admin.php?page=gutslider-license') ) : esc_url('https://gutslider.com/pricing'); ?>" class="nav-link" target="<?php echo $has_pro ? esc_attr('_self') : esc_attr('_blank'); ?>">
                                <?php if( $has_pro): ?>
                                    <?php esc_html_e('License', 'slider-blocks'); ?>
                                <?php else: ?>
                                    <?php esc_html_e('Get License', 'slider-blocks'); ?>
                                <?php endif; ?>
                            </a>
                        </div>
                     </div>
                    <div class="header-actions flex align-center gap-8">
                        <?php if ( ! $has_pro ) : ?>
                            <a href="https://gutslider.com/pricing" target="_blank" class="header-btn pro-btn"><?php esc_html_e('Get Pro', 'slider-blocks'); ?></a>
                        <?php endif; ?>
                        <a href="https://gutslider.com/docs" target="_blank" class="log-btn">
                            <span class="dashicons dashicons-external"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="content-area">
                <div class="container">
                    <div class="supports-area m50 flex gap-16">
                        <div class="gp-card bg-white p20">
                            <div class="icon flex align-center gap-8">
                                <span class="dashicons dashicons-sos"></span>
                                <h3 class="support-title"><?php esc_html_e('Need Help?', 'slider-blocks'); ?></h3>
                            </div>
                            <div class="content">
                                <p class="support-desc"><?php esc_html_e('Check out our documentation or reach out to our support team for assistance.', 'slider-blocks'); ?></p>
                                <a href="https://gutslider.com/docs" target="_blank" class="support-link"><?php esc_html_e('View Documentation', 'slider-blocks'); ?></a>
                            </div>
                        </div>
                        <div class="gp-card p20 bg-white">
                            <div class="icon flex align-center gap-8">
                                <span class="dashicons dashicons-tickets-alt"></span>
                                <h3 class="support-title"><?php esc_html_e('Submit a Ticket', 'slider-blocks'); ?></h3>
                            </div>
                            <div class="content">
                                <p class="support-desc"><?php esc_html_e('If you need further assistance, please submit a support ticket.', 'slider-blocks'); ?></p>
                                <a href="https://gutslider.com/support" target="_blank" class="support-link"><?php esc_html_e('Submit a Ticket', 'slider-blocks'); ?></a>
                            </div>
                        </div>
                        <div class="gp-card p20 bg-white">
                            <div class="icon flex align-center gap-8">
                                <span class="dashicons dashicons-share-alt2"></span>
                                <h3 class="support-title"><?php esc_html_e('Spread Your Love', 'slider-blocks'); ?></h3>
                            </div>
                            <div class="content">
                                <p class="support-desc"><?php esc_html_e('If you love GutSlider, please consider sharing it with your friends and colleagues!', 'slider-blocks'); ?></p>
                                <a href="https://wordpress.org/support/plugin/slider-blocks/reviews/" target="_blank" class="support-link"><?php esc_html_e('Write a Review', 'slider-blocks'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="welcome-area flex gap-30">
                        <div class="video-area bg-white p30 border rounded">
                            <h3 class="video-title">
                                <?php esc_html_e('Welcome to GutSlider Blocks', 'slider-blocks'); ?>
                            </h3>
                            <p class="support-desc">
                                <?php esc_html_e('Watch the video below to get started with GutSlider and learn how to create stunning sliders using our blocks.', 'slider-blocks'); ?>
                            </p>
                            <iframe width="560" height="315" src="https://www.youtube.com/embed/P9Zj4bSVq4I?si=HnOCmeQxQ-96hdG9" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                        </div>
                        <div class="our-products">
                            <div class="gp-card bg-white p20">
                                <div class="icon flex align-center gap-8">
                                    <span class="dashicons dashicons-layout"></span>
                                    <h3 class="support-title"><?php esc_html_e('GutenLayouts', 'slider-blocks'); ?></h3>
                                </div>
                                <div class="content">
                                    <p class="support-desc"><?php esc_html_e('Design stunning layouts with our pre-built templates and patterns with core blocks.', 'slider-blocks'); ?></p>
                                    <a href="https://gutenlayouts.com" target="_blank" class="support-link"><?php esc_html_e('Check it out', 'slider-blocks'); ?></a>
                                </div>
                            </div>
                            <div class="gp-card p20 bg-white">
                                <div class="icon flex align-center gap-8">
                                    <span class="dashicons dashicons-admin-plugins"></span>
                                    <h3 class="support-title"><?php esc_html_e('Easy Accordion', 'slider-blocks'); ?></h3>
                                </div>
                                <div class="content">
                                    <p class="support-desc"><?php esc_html_e('Create beautiful and responsive accordions with ease using our Easy Accordion block.', 'slider-blocks'); ?></p>
                                    <a href="https://accordion.gutenbergkits.com" target="_blank" class="support-link"><?php esc_html_e('Check it out', 'slider-blocks'); ?></a>
                                </div>
                            </div>
                            <div class="gp-card p20 bg-white">
                                <div class="icon flex align-center gap-8">
                                    <span class="dashicons dashicons-admin-plugins"></span>
                                    <h3 class="support-title"><?php esc_html_e('Gmap Block', 'slider-blocks'); ?></h3>
                                </div>
                                <div class="content">
                                    <p class="support-desc"><?php esc_html_e('Display interactive Google Maps with ease using our Gmap Block.', 'slider-blocks'); ?></p>
                                    <a href="https://gmap.gutenbergkits.com" target="_blank" class="support-link"><?php esc_html_e('Check it out', 'slider-blocks'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if( ! $has_pro ) : ?>
                    <div class="welcome-box bg-white p20 mt50 border rounded">
                        <h3 class="video-title"><?php esc_html_e('Get Started with GutSlider Pro', 'slider-blocks'); ?></h3>
                        <p class="support-desc"><?php esc_html_e('Unlock the full potential of GutSlider by upgrading to the Pro version. Enjoy premium blocks, advanced features, and priority support.', 'slider-blocks'); ?></p>
                        <a href="https://gutslider.com/pricing" target="_blank" class="welcome-link pro-btn"><?php esc_html_e('Upgrade to Pro', 'slider-blocks'); ?></a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
    
        /**
         * Render blocks page
         */
        public function render_blocks_page() {
            $blocks = $this->get_blocks();
            $has_pro = defined('GUTSLIDER_PRO_VERSION');
            ?>
            <div class="gutslider-admin-wrap">
                <div class="header bg-white box-shadow">
                    <div class="container flex align-center">
                        <div class="header-content flex flex-1 align-center">
                            <div class="logo-area flex align-center gap-8">
                                <img src="<?php echo esc_url( GUTSLIDER_URL . 'admin/img/gutslider.svg' ); ?>" alt="<?php esc_attr_e('GutSlider Logo', 'slider-blocks'); ?>" class="logo-img"/>
                            </div>
                            <div class="nav flex align-center">
                                <a href="<?php echo esc_url( admin_url('admin.php?page=gutslider-blocks') ); ?>" class="nav-link"><?php esc_html_e('Welcome', 'slider-blocks'); ?></a>
                                <span class="dot"></span>
                                <a href="<?php echo esc_url( admin_url('admin.php?page=gutslider-blocks-settings') ); ?>" class="nav-link"><?php esc_html_e('Blocks', 'slider-blocks'); ?></a>
                                <span class="dot"></span>
                                <a href="<?php echo $has_pro ? esc_url( admin_url('admin.php?page=gutslider-license') ) : esc_url('https://gutslider.com/pricing'); ?>" class="nav-link" target="<?php echo $has_pro ? esc_attr('_self') : esc_attr('_blank'); ?>">
                                    <?php if( $has_pro): ?>
                                        <?php esc_html_e('License', 'slider-blocks'); ?>
                                    <?php else: ?>
                                        <?php esc_html_e('Get License', 'slider-blocks'); ?>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                        <div class="header-actions flex align-center gap-8">
                            <?php if ( ! $has_pro ) : ?>
                                <a href="https://gutslider.com/pricing" target="_blank" class="header-btn pro-btn"><?php esc_html_e('Get Pro', 'slider-blocks'); ?></a>
                            <?php endif; ?>
                            <a href="https://gutslider.com/docs" target="_blank" class="log-btn">
                                <span class="dashicons dashicons-external"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="content-area">
                    <div class="container">
                        <div class="supports-area m50 flex gap-16">
                            <div class="gp-card bg-white p20">
                                <div class="icon flex align-center gap-8">
                                    <span class="dashicons dashicons-sos"></span>
                                    <h3 class="support-title"><?php esc_html_e('Need Help?', 'slider-blocks'); ?></h3>
                                </div>
                                <div class="content">
                                    <p class="support-desc"><?php esc_html_e('Check out our documentation or reach out to our support team for assistance.', 'slider-blocks'); ?></p>
                                    <a href="https://gutslider.com/docs" target="_blank" class="support-link"><?php esc_html_e('View Documentation', 'slider-blocks'); ?></a>
                                </div>
                            </div>
                            <div class="gp-card p20 bg-white">
                                <div class="icon flex align-center gap-8">
                                    <span class="dashicons dashicons-tickets-alt"></span>
                                    <h3 class="support-title"><?php esc_html_e('Submit a Ticket', 'slider-blocks'); ?></h3>
                                </div>
                                <div class="content">
                                    <p class="support-desc"><?php esc_html_e('If you need further assistance, please submit a support ticket.', 'slider-blocks'); ?></p>
                                    <a href="https://gutslider.com/support" target="_blank" class="support-link"><?php esc_html_e('Submit a Ticket', 'slider-blocks'); ?></a>
                                </div>
                            </div>
                            <div class="gp-card p20 bg-white">
                                <div class="icon flex align-center gap-8">
                                    <span class="dashicons dashicons-share-alt2"></span>
                                    <h3 class="support-title"><?php esc_html_e('Spread Your Love', 'slider-blocks'); ?></h3>
                                </div>
                                <div class="content">
                                    <p class="support-desc"><?php esc_html_e('If you love GutSlider, please consider sharing it with your friends and colleagues!', 'slider-blocks'); ?></p>
                                    <a href="https://wordpress.org/support/plugin/slider-blocks/reviews/" target="_blank" class="support-link"><?php esc_html_e('Write a Review', 'slider-blocks'); ?></a>
                                </div>
                            </div>
                        </div>
                        <h2 class="page-title mb30"><?php esc_html_e('Manage GutSlider Blocks', 'slider-blocks'); ?></h2>
                        <div class="gutslider-blocks-grid">
                            <?php foreach ($blocks as $block): ?>
                                <?php 
                                    $is_pro = isset($block['is_pro']) && $block['is_pro'];
                                    $is_active = isset($block['active']) && $block['active'];
                                    $block_key = 'gut_' . str_replace('-', '_', $block['name']);
                                    $saved_status = get_option($block_key, true);
                                    $is_checked = $saved_status ? 'checked' : '';
                                    $demo_url = 'https://demos.gutslider.com/' . $block['demo_slug'];
                                ?>
                                <div class="gutslider-block-card <?php echo $is_pro ? 'pro-block' : ''; ?>">
                                    <?php if ($is_pro): ?>
                                        <span class="pro-badge"><?php esc_html_e('PRO', 'slider-blocks'); ?></span>
                                    <?php else: ?>
                                        <span class="pro-badge free-badge"><?php esc_html_e('FREE', 'slider-blocks'); ?></span>
                                    <?php endif; ?>
                                    
                                    <div class="block-header">
                                        <h3 class="block-title"><?php echo esc_html($block['title']); ?></h3>
                                    </div>

                                    <p class="block-description"><?php echo esc_html($block['description']); ?></p>

                                    <div class="block-footer">
                                        <a href="<?php echo esc_url($demo_url); ?>" 
                                        class="demo-link" 
                                        target="_blank" 
                                        rel="noopener noreferrer">
                                            <?php esc_html_e('View Demo', 'slider-blocks'); ?>
                                            <span class="dashicons dashicons-external"></span>
                                        </a>
                                        <label class="toggle-switch">
                                            <input 
                                                type="checkbox" 
                                                class="block-toggle" 
                                                data-block="<?php echo esc_attr($block['name']); ?>"
                                                data-key="<?php echo esc_attr($block_key); ?>"
                                                <?php echo esc_attr($is_checked); ?>
                                                <?php echo $is_pro && !defined('GUTSLIDER_PRO_VERSION') ? 'disabled' : ''; ?>
                                            >
                                            <span class="toggle-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Get blocks data
         */
        private function get_blocks() {
            $blocks_file = GUTSLIDER_DIR . '/includes/api/blocks.php';
            if (file_exists($blocks_file)) {
                return include $blocks_file;
            }
            return [];
        }

        /**
         * AJAX handler for toggling block status
         */
        public function toggle_block_status() {
            check_ajax_referer('gutslider_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Permission denied', 'slider-blocks')]);
            }

            $block_key = isset($_POST['block_key']) ? sanitize_text_field( wp_unslash($_POST['block_key']) ) : '';
            $status = isset($_POST['status']) ? rest_sanitize_boolean($_POST['status']) : false;

            if (empty($block_key)) {
                wp_send_json_error(['message' => __('Invalid block key', 'slider-blocks')]);
            }

            update_option($block_key, $status);
            wp_send_json_success(['message' => __('Block status updated', 'slider-blocks')]);
        }

        /**
         * Register block settings
         */
        public function register_settings() {
            foreach (self::BLOCK_SETTINGS as $setting) {
                register_setting('rest-api-settings', $setting, [
                    'type' => 'boolean',
                    'default' => true,
                    'show_in_rest' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ]);
            }
        }

        /**
         * Enqueue admin assets
         */
        private function enqueue_admin_assets() {
            wp_enqueue_script('gutslider-admin-script', GUTSLIDER_URL . 'admin/js/admin.js', array('jquery'), GUTSLIDER_VERSION, true);
            wp_enqueue_style('gutslider-admin-style', GUTSLIDER_URL . 'admin/css/admin.css', [], GUTSLIDER_VERSION);
            wp_localize_script('gutslider-admin-script', 'gutslider', [
                'version' => GUTSLIDER_VERSION,
                'nonce' => wp_create_nonce('gutslider_nonce'),
                'isPro' => defined('GUTSLIDER_PRO_VERSION'),
                'proVersion' => defined('GUTSLIDER_PRO_VERSION') ? GUTSLIDER_PRO_VERSION : '',
                'ajaxUrl' => admin_url('admin-ajax.php'),
            ]);
        }
    }
}

new GutSlider_Admin();