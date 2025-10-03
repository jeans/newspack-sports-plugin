(function($) {
    'use strict';

    class NewspackSportsAdmin {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.setupSyncButtons();
        }

        bindEvents() {
            // API key visibility toggle
            $('input[type="password"]').each(function() {
                const $input = $(this);
                const $toggle = $('<button type="button" class="button button-small" style="margin-left: 5px;">Show</button>');
                
                $toggle.on('click', function() {
                    const isPassword = $input.attr('type') === 'password';
                    $input.attr('type', isPassword ? 'text' : 'password');
                    $toggle.text(isPassword ? 'Hide' : 'Show');
                });
                
                $input.after($toggle);
            });

            // Import type change
            $('#import_type').on('change', this.handleImportTypeChange.bind(this));
        }

        handleImportTypeChange(e) {
            const type = $(e.target).val();
            this.updateImportDescription(type);
        }

        updateImportDescription(type) {
            const descriptions = {
                teams: 'CSV format: name,slug,sport,competition,founded,stadium,description',
                matches: 'CSV format: title,date,time,home_team,away_team,sport,competition',
                players: 'CSV format: name,number,position,team,birthdate,sport,competition'
            };

            $('.import-section .description').text(descriptions[type] || 'Select an import type for format details.');
        }

        setupSyncButtons() {
            $('.sync-actions .button').on('click', this.handleSyncClick.bind(this));
        }

        handleSyncClick(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const syncType = $button.attr('id').replace('sync-', '');
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(newspack_sports_admin.strings.syncing);
            
            $.ajax({
                url: newspack_sports_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'newspack_sports_sync_data',
                    type: syncType,
                    nonce: newspack_sports_admin.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $button.text(newspack_sports_admin.strings.synced);
                        this.showNotice('success', response.data.message || 'Sync completed successfully.');
                    } else {
                        $button.text(originalText);
                        this.showNotice('error', response.data || 'Sync failed.');
                    }
                },
                error: () => {
                    $button.text(originalText);
                    this.showNotice('error', newspack_sports_admin.strings.error);
                },
                complete: () => {
                    setTimeout(() => {
                        $button.prop('disabled', false).text(originalText);
                    }, 2000);
                }
            });
        }

        showNotice(type, message) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const $notice = $(
                `<div class="notice ${noticeClass} is-dismissible" style="margin-top: 15px;">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>`
            );
            
            $('.sync-actions').after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Manual dismiss
            $notice.find('.notice-dismiss').on('click', function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        new NewspackSportsAdmin();
    });

})(jQuery);