<?php
/**
 * The shortcode functionality of the plugin.
 *
 * @since      1.0.0
 */
class Varabit_SEO_Audit_Shortcode {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        add_shortcode('varabit_seo_audit', array($this, 'render_shortcode'));
        add_action('wp_ajax_varabit_run_seo_audit', array($this, 'run_seo_audit'));
        add_action('wp_ajax_nopriv_varabit_run_seo_audit', array($this, 'run_seo_audit'));
    }

    /**
     * Render the shortcode output.
     *
     * @return string Shortcode HTML output.
     */
    public function render_shortcode($atts) {
        // Extract shortcode attributes
        $atts = shortcode_atts(
            array(
                'title' => 'SEO Audit Tool',
            ),
            $atts,
            'varabit_seo_audit'
        );
        
        // Add newsletter shortcode from settings
        $newsletter_shortcode = get_option('varabit_seo_audit_newsletter_shortcode', '');

        // Start output buffering
        ob_start();
        ?>
        <div class="varabit-seo-audit-container">
            <h2><?php echo esc_html($atts['title']); ?></h2>
            
            <div class="varabit-seo-audit-form">
                <form id="varabit-seo-audit-form" method="post">
                    <div class="form-group">
                        <label for="website-url">Enter Website URL:</label>
                        <input type="url" id="website-url" name="website-url" placeholder="https://example.com" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" id="run-audit-btn">Run SEO Audit</button>
                    </div>
                </form>
            </div>
            
            <div class="varabit-seo-audit-loading" style="display: none;">
                <div class="spinner"></div>
                <p>Running SEO audit. This may take a minute...</p>
            </div>
            
            <div class="varabit-seo-audit-results" style="display: none;">
                <h3>SEO Audit Results</h3>
                
                <div class="audit-summary">
                    <div class="audit-score"></div>
                    <div class="audit-summary-text"></div>
                </div>
                
                <div class="audit-sections">
                    <div class="audit-section" id="pagespeed-section">
                        <h4>Page Speed</h4>
                        <div class="section-content"></div>
                    </div>
                    
                    <div class="audit-section" id="meta-tags-section">
                        <h4>Meta Tags</h4>
                        <div class="section-content"></div>
                    </div>
                    
                    <div class="audit-section" id="headings-section">
                        <h4>Headings Structure</h4>
                        <div class="section-content"></div>
                    </div>
                    
                    <div class="audit-section" id="images-section">
                        <h4>Image Alt Texts</h4>
                        <div class="section-content"></div>
                    </div>
                    
                    <div class="audit-section" id="mobile-section">
                        <h4>Mobile-Friendliness</h4>
                        <div class="section-content"></div>
                    </div>
                    
                    <div class="audit-section" id="keywords-section">
                        <h4>Keywords Analysis</h4>
                        <div class="section-content"></div>
                    </div>
                    
                    <div class="audit-section" id="errors-section">
                        <h4>Errors & Warnings</h4>
                        <div class="section-content"></div>
                    </div>
                </div>
                
                <div class="audit-newsletter">
                    <h4>Stay Updated with SEO Tips</h4>
                    <?php 
                    $shortcode = get_option('varabit_seo_audit_newsletter_shortcode', '');
                    if (!empty($shortcode)) {
                        echo do_shortcode($shortcode);
                    }
                    ?>
                </div>
                
                <div class="audit-actions">
                    <button id="download-pdf-btn">Download PDF Report</button>
                </div>
            </div>
            
            <div class="varabit-seo-audit-error" style="display: none;">
                <div class="error-message"></div>
            </div>
        </div>
        <?php
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * AJAX handler for running the SEO audit.
     */
    public function run_seo_audit() {
        // Check nonce for security
        check_ajax_referer('varabit_seo_audit_nonce', 'nonce');
        
        // Get the website URL from the request
        $website_url = isset($_POST['website_url']) ? esc_url_raw($_POST['website_url']) : '';
        
        if (empty($website_url)) {
            wp_send_json_error(array('message' => 'Please provide a valid URL.'));
            return;
        }
        
        // Initialize the API class
        $api = new Varabit_SEO_Audit_API();
        
        try {
            // Run the PageSpeed analysis
            $pagespeed_results = $api->get_pagespeed_insights($website_url);
            
            // Fetch and analyze the website content
            $content_analysis = $api->analyze_website_content($website_url);
            
            // Combine all results
            $results = array(
                'pagespeed' => $pagespeed_results,
                'meta_tags' => $content_analysis['meta_tags'],
                'headings' => $content_analysis['headings'],
                'images' => $content_analysis['images'],
                'mobile' => $pagespeed_results['mobile'],
                'keywords' => $content_analysis['keywords'],
                'errors' => array_merge($pagespeed_results['errors'], $content_analysis['errors']),
                'score' => $this->calculate_overall_score($pagespeed_results, $content_analysis),
            );
            
            wp_send_json_success($results);
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * Calculate the overall SEO score based on all audit results.
     *
     * @param array $pagespeed_results PageSpeed Insights results.
     * @param array $content_analysis Content analysis results.
     * @return int Overall score (0-100).
     */
    private function calculate_overall_score($pagespeed_results, $content_analysis) {
        // This is a simplified scoring algorithm
        $scores = array();
        
        // PageSpeed score (0-100)
        $scores[] = isset($pagespeed_results['desktop']['score']) ? $pagespeed_results['desktop']['score'] * 100 : 0;
        $scores[] = isset($pagespeed_results['mobile']['score']) ? $pagespeed_results['mobile']['score'] * 100 : 0;
        
        // Meta tags score (0-100)
        $meta_score = 0;
        if (!empty($content_analysis['meta_tags']['title'])) $meta_score += 50;
        if (!empty($content_analysis['meta_tags']['description'])) $meta_score += 50;
        $scores[] = $meta_score;
        
        // Headings score (0-100)
        $heading_score = 0;
        if (!empty($content_analysis['headings']['h1'])) $heading_score += 40;
        if (!empty($content_analysis['headings']['h2'])) $heading_score += 30;
        if (!empty($content_analysis['headings']['h3'])) $heading_score += 30;
        $scores[] = $heading_score;
        
        // Images score (0-100)
        $img_score = 0;
        $total_images = count($content_analysis['images']);
        $images_with_alt = 0;
        
        foreach ($content_analysis['images'] as $image) {
            if (!empty($image['alt'])) $images_with_alt++;
        }
        
        if ($total_images > 0) {
            $img_score = ($images_with_alt / $total_images) * 100;
        } else {
            $img_score = 100; // No images is not a problem
        }
        $scores[] = $img_score;
        
        // Calculate average score
        return round(array_sum($scores) / count($scores));
    }
}