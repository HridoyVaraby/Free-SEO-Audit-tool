(function($) {
    'use strict';

    /**
     * Initialize the SEO Audit Tool functionality.
     */
    function initSeoAuditTool() {
        const $form = $('#varabit-seo-audit-form');
        const $results = $('.varabit-seo-audit-results');
        const $loading = $('.varabit-seo-audit-loading');
        const $error = $('.varabit-seo-audit-error');

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            runSeoAudit();
        });

        // Handle PDF download
        $('#download-pdf-btn').on('click', function() {
            generatePDF();
        });

        /**
         * Run the SEO audit via AJAX.
         */
        function runSeoAudit() {
            const websiteUrl = $('#website-url').val();
            
            // Show loading indicator
            $form.hide();
            $results.hide();
            $error.hide();
            $loading.show();

            // Make AJAX request
            $.ajax({
                url: varabit_seo_audit.ajax_url,
                type: 'POST',
                data: {
                    action: 'varabit_run_seo_audit',
                    nonce: varabit_seo_audit.nonce,
                    website_url: websiteUrl
                },
                success: function(response) {
                    $loading.hide();
                    
                    if (response.success) {
                        displayResults(response.data);
                    } else {
                        displayError(response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    $loading.hide();
                    displayError('An error occurred: ' + error);
                }
            });
        }

        /**
         * Display the SEO audit results.
         */
        function displayResults(data) {
            // Display overall score
            $('.audit-score').html('<span class="score-value">' + data.score + '</span>');
            
            // Add score class based on value
            let scoreClass = 'poor';
            if (data.score >= 90) {
                scoreClass = 'excellent';
            } else if (data.score >= 70) {
                scoreClass = 'good';
            } else if (data.score >= 50) {
                scoreClass = 'average';
            }
            $('.audit-score').addClass('score-' + scoreClass);
            
            // Set summary text
            let summaryText = 'Your website needs significant improvements.';
            if (data.score >= 90) {
                summaryText = 'Excellent! Your website is well-optimized for SEO.';
            } else if (data.score >= 70) {
                summaryText = 'Good job! Your website is performing well but has room for improvement.';
            } else if (data.score >= 50) {
                summaryText = 'Your website needs some improvements to optimize for SEO.';
            }
            $('.audit-summary-text').text(summaryText);
            
            // Display PageSpeed results
            displayPageSpeedResults(data.pagespeed);
            
            // Display Meta Tags results
            displayMetaTagsResults(data.meta_tags);
            
            // Display Headings results
            displayHeadingsResults(data.headings);
            
            // Display Images results
            displayImagesResults(data.images);
            
            // Display Mobile-Friendliness results
            displayMobileResults(data.mobile);
            
            // Display Keywords results
            displayKeywordsResults(data.keywords);
            
            // Display Errors & Warnings
            displayErrorsWarnings(data.errors);
            
            // Show results
            $results.show();
        }

        /**
         * Display PageSpeed results.
         */
        function displayPageSpeedResults(pagespeed) {
            let html = '<div class="pagespeed-scores">';
            
            // Desktop score
            html += '<div class="pagespeed-score">';
            html += '<h5>Desktop</h5>';
            html += '<div class="score-circle score-' + getScoreClass(pagespeed.desktop.score) + '">' + Math.round(pagespeed.desktop.score * 100) + '</div>';
            html += '</div>';
            
            // Mobile score
            html += '<div class="pagespeed-score">';
            html += '<h5>Mobile</h5>';
            html += '<div class="score-circle score-' + getScoreClass(pagespeed.mobile.score) + '">' + Math.round(pagespeed.mobile.score * 100) + '</div>';
            html += '</div>';
            
            html += '</div>';
            
            // Core Web Vitals
            if (pagespeed.desktop.metrics && Object.keys(pagespeed.desktop.metrics).length > 0) {
                html += '<h5>Core Web Vitals (Desktop)</h5>';
                html += '<table class="metrics-table">';
                html += '<tr><th>Metric</th><th>Value</th></tr>';
                
                for (const [key, metric] of Object.entries(pagespeed.desktop.metrics)) {
                    html += '<tr>';
                    html += '<td>' + metric.name + '</td>';
                    html += '<td class="metric-value score-' + getScoreClass(metric.score) + '">' + metric.value + '</td>';
                    html += '</tr>';
                }
                
                html += '</table>';
            }
            
            // Improvement opportunities
            if (pagespeed.desktop.opportunities && pagespeed.desktop.opportunities.length > 0) {
                html += '<h5>Improvement Opportunities</h5>';
                html += '<ul class="opportunities-list">';
                
                for (const opportunity of pagespeed.desktop.opportunities) {
                    html += '<li>' + opportunity.name + '</li>';
                }
                
                html += '</ul>';
            }
            
            $('#pagespeed-section .section-content').html(html);
        }

        /**
         * Display Meta Tags results.
         */
        function displayMetaTagsResults(metaTags) {
            let html = '<table class="meta-tags-table">';
            
            // Title
            html += '<tr>';
            html += '<th>Title</th>';
            html += '<td>' + (metaTags.title || 'Not found') + '</td>';
            html += '</tr>';
            
            // Description
            html += '<tr>';
            html += '<th>Description</th>';
            html += '<td>' + (metaTags.description || 'Not found') + '</td>';
            html += '</tr>';
            
            // Robots
            html += '<tr>';
            html += '<th>Robots</th>';
            html += '<td>' + (metaTags.robots || 'Not specified') + '</td>';
            html += '</tr>';
            
            // Canonical
            html += '<tr>';
            html += '<th>Canonical URL</th>';
            html += '<td>' + (metaTags.canonical || 'Not specified') + '</td>';
            html += '</tr>';
            
            html += '</table>';
            
            // Issues
            if (metaTags.issues && metaTags.issues.length > 0) {
                html += '<h5>Issues</h5>';
                html += '<ul class="issues-list">';
                
                for (const issue of metaTags.issues) {
                    html += '<li class="' + issue.type + '">' + issue.message + '</li>';
                }
                
                html += '</ul>';
            }
            
            $('#meta-tags-section .section-content').html(html);
        }

        /**
         * Display Headings results.
         */
        function displayHeadingsResults(headings) {
            let html = '<div class="headings-summary">';
            
            // Count of each heading type
            html += '<table class="headings-count">';
            html += '<tr>';
            for (let i = 1; i <= 6; i++) {
                html += '<th>H' + i + '</th>';
            }
            html += '</tr>';
            html += '<tr>';
            for (let i = 1; i <= 6; i++) {
                const count = headings['h' + i] ? headings['h' + i].length : 0;
                html += '<td>' + count + '</td>';
            }
            html += '</tr>';
            html += '</table>';
            html += '</div>';
            
            // H1 headings
            if (headings.h1 && headings.h1.length > 0) {
                html += '<h5>H1 Headings</h5>';
                html += '<ul>';
                for (const heading of headings.h1) {
                    html += '<li>' + heading + '</li>';
                }
                html += '</ul>';
            }
            
            // H2 headings
            if (headings.h2 && headings.h2.length > 0) {
                html += '<h5>H2 Headings</h5>';
                html += '<ul>';
                for (const heading of headings.h2) {
                    html += '<li>' + heading + '</li>';
                }
                html += '</ul>';
            }
            
            // Issues
            if (headings.issues && headings.issues.length > 0) {
                html += '<h5>Issues</h5>';
                html += '<ul class="issues-list">';
                
                for (const issue of headings.issues) {
                    html += '<li class="' + issue.type + '">' + issue.message + '</li>';
                }
                
                html += '</ul>';
            }
            
            $('#headings-section .section-content').html(html);
        }

        /**
         * Display Images results.
         */
        function displayImagesResults(images) {
            // Count images with and without alt text
            const totalImages = images.length;
            let imagesWithAlt = 0;
            
            for (const image of images) {
                if (image.has_alt) {
                    imagesWithAlt++;
                }
            }
            
            const altPercentage = totalImages > 0 ? Math.round((imagesWithAlt / totalImages) * 100) : 100;
            
            let html = '<div class="images-summary">';
            html += '<p>Total Images: <strong>' + totalImages + '</strong></p>';
            html += '<p>Images with Alt Text: <strong>' + imagesWithAlt + ' (' + altPercentage + '%)</strong></p>';
            html += '</div>';
            
            // Images list
            if (totalImages > 0) {
                html += '<h5>Images</h5>';
                html += '<table class="images-table">';
                html += '<tr><th>Image</th><th>Alt Text</th></tr>';
                
                for (const image of images) {
                    html += '<tr>';
                    html += '<td><img src="' + image.src + '" alt="" style="max-width: 100px; max-height: 60px;"></td>';
                    html += '<td class="' + (image.has_alt ? 'has-alt' : 'no-alt') + '">' + (image.alt || 'No alt text') + '</td>';
                    html += '</tr>';
                }
                
                html += '</table>';
            }
            
            $('#images-section .section-content').html(html);
        }

        /**
         * Display Mobile-Friendliness results.
         */
        function displayMobileResults(mobile) {
            let html = '<div class="mobile-score">';
            html += '<div class="score-circle score-' + getScoreClass(mobile.score) + '">' + Math.round(mobile.score * 100) + '</div>';
            html += '<p>Mobile Usability Score</p>';
            html += '</div>';
            
            // Mobile metrics
            if (mobile.metrics && Object.keys(mobile.metrics).length > 0) {
                html += '<h5>Mobile Metrics</h5>';
                html += '<table class="metrics-table">';
                html += '<tr><th>Metric</th><th>Value</th></tr>';
                
                for (const [key, metric] of Object.entries(mobile.metrics)) {
                    html += '<tr>';
                    html += '<td>' + metric.name + '</td>';
                    html += '<td class="metric-value score-' + getScoreClass(metric.score) + '">' + metric.value + '</td>';
                    html += '</tr>';
                }
                
                html += '</table>';
            }
            
            // Improvement opportunities
            if (mobile.opportunities && mobile.opportunities.length > 0) {
                html += '<h5>Mobile Improvement Opportunities</h5>';
                html += '<ul class="opportunities-list">';
                
                for (const opportunity of mobile.opportunities) {
                    html += '<li>' + opportunity.name + '</li>';
                }
                
                html += '</ul>';
            }
            
            $('#mobile-section .section-content').html(html);
        }

        /**
         * Display Keywords results.
         */
        function displayKeywordsResults(keywords) {
            let html = '';
            
            // Top keywords
            if (keywords.top_keywords && Object.keys(keywords.top_keywords).length > 0) {
                html += '<h5>Top Keywords</h5>';
                html += '<table class="keywords-table">';
                html += '<tr><th>Keyword</th><th>Count</th><th>Density</th></tr>';
                
                for (const [keyword, count] of Object.entries(keywords.top_keywords)) {
                    const density = keywords.density[keyword] || 0;
                    html += '<tr>';
                    html += '<td>' + keyword + '</td>';
                    html += '<td>' + count + '</td>';
                    html += '<td>' + density + '%</td>';
                    html += '</tr>';
                }
                
                html += '</table>';
            }
            
            // Issues
            if (keywords.issues && keywords.issues.length > 0) {
                html += '<h5>Issues</h5>';
                html += '<ul class="issues-list">';
                
                for (const issue of keywords.issues) {
                    html += '<li class="' + issue.type + '">' + issue.message + '</li>';
                }
                
                html += '</ul>';
            }
            
            $('#keywords-section .section-content').html(html);
        }

        /**
         * Display Errors & Warnings.
         */
        function displayErrorsWarnings(errors) {
            if (!errors || errors.length === 0) {
                $('#errors-section .section-content').html('<p>No errors or warnings found.</p>');
                return;
            }
            
            let html = '<ul class="issues-list">';
            
            for (const error of errors) {
                html += '<li class="' + error.type + '">' + error.message + '</li>';
            }
            
            html += '</ul>';
            
            $('#errors-section .section-content').html(html);
        }

        /**
         * Display error message.
         */
        function displayError(message) {
            $error.find('.error-message').text(message);
            $error.show();
            $form.show();
        }

        /**
         * Generate PDF report.
         */
        function generatePDF() {
            // This is a simplified version. In a real implementation, you would use a library like jsPDF
            // or make an AJAX call to a server-side PDF generation function.
            alert('PDF generation would be implemented here. This would typically use a library like jsPDF or a server-side PDF generation function.');
        }

        /**
         * Get CSS class based on score.
         */
        function getScoreClass(score) {
            if (score >= 0.9) {
                return 'excellent';
            } else if (score >= 0.7) {
                return 'good';
            } else if (score >= 0.5) {
                return 'average';
            } else {
                return 'poor';
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initSeoAuditTool();
    });

})(jQuery);