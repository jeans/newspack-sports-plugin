(function($) {
    'use strict';

    class NewspackSportsFrontend {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.setupSportsTabs();
            this.setupDataBlocks();
        }

        bindEvents() {
            // Smooth scrolling for anchor links
            $('a[href^="#"]').on('click', this.handleSmoothScroll.bind(this));

            // Lazy loading for images
            this.setupLazyLoading();

            // Match countdown timers
            this.setupMatchTimers();
        }

        setupSportsTabs() {
            const $tabs = $('.sports-tabs-list');
            
            if ($tabs.length) {
                // Handle tab click for mobile
                $tabs.on('click', '.sports-tab-link', (e) => {
                    if (window.innerWidth < 768) {
                        e.preventDefault();
                        const $tab = $(e.currentTarget).parent();
                        $tabs.find('.sports-tab-item').removeClass('active');
                        $tab.addClass('active');
                    }
                });

                // Sticky tabs behavior
                $(window).on('scroll', this.handleStickyTabs.bind(this));
            }
        }

        handleStickyTabs() {
            const $tabs = $('.sports-tabs-navigation');
            const scrollTop = $(window).scrollTop();
            const offsetTop = $tabs.offset().top;

            if (scrollTop > offsetTop) {
                $tabs.addClass('sticky-active');
            } else {
                $tabs.removeClass('sticky-active');
            }
        }

        setupDataBlocks() {
            // Enhance Remote Data Blocks with sports-specific functionality
            $(document).on('remotedatablocks:loaded', '.sports-data-block', (e) => {
                const $block = $(e.target);
                this.enhanceSportsDataBlock($block);
            });

            // Auto-refresh for live data
            this.setupAutoRefresh();
        }

        enhanceSportsDataBlock($block) {
            const dataType = $block.data('data-type');
            
            switch (dataType) {
                case 'standings':
                    this.enhanceStandingsBlock($block);
                    break;
                case 'schedule':
                    this.enhanceScheduleBlock($block);
                    break;
                case 'results':
                    this.enhanceResultsBlock($block);
                    break;
            }
        }

        enhanceStandingsBlock($block) {
            // Add team logos and links if available
            $block.find('tr').each((index, row) => {
                if (index === 0) return; // Skip header
                
                const $row = $(row);
                const teamName = $row.find('td:nth-child(2)').text().trim();
                
                // Try to find team and add logo
                this.findTeamByName(teamName).then(team => {
                    if (team && team.logo) {
                        $row.find('td:nth-child(2)').prepend(
                            `<img src="${team.logo}" alt="${teamName}" class="team-logo-small" style="width: 20px; height: 20px; margin-right: 8px; vertical-align: middle;">`
                        );
                    }
                    
                    if (team && team.url) {
                        $row.find('td:nth-child(2)').html(
                            `<a href="${team.url}" style="color: inherit; text-decoration: none;">${$row.find('td:nth-child(2)').html()}</a>`
                        );
                    }
                });
            });
        }

        enhanceScheduleBlock($block) {
            // Add countdown timers for upcoming matches
            $block.find('.match-date').each((index, element) => {
                const $date = $(element);
                const dateText = $date.text().trim();
                const matchDate = new Date(dateText);
                
                if (matchDate > new Date()) {
                    this.addCountdownTimer($date, matchDate);
                }
            });
        }

        enhanceResultsBlock($block) {
            // Highlight winning teams
            $block.find('.match-result').each((index, element) => {
                const $result = $(element);
                const scoreText = $result.text().trim();
                const scores = scoreText.split('-').map(score => parseInt(score.trim()));
                
                if (scores.length === 2 && !isNaN(scores[0]) && !isNaN(scores[1])) {
                    if (scores[0] > scores[1]) {
                        $result.closest('.match-item').find('.home-team').addClass('winner');
                    } else if (scores[1] > scores[0]) {
                        $result.closest('.match-item').find('.away-team').addClass('winner');
                    }
                }
            });
        }

        findTeamByName(teamName) {
            // This would typically make an AJAX call to find team data
            return new Promise(resolve => {
                $.ajax({
                    url: newspack_sports_frontend.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'newspack_sports_find_team',
                        team_name: teamName,
                        nonce: newspack_sports_frontend.nonce
                    },
                    success: (response) => {
                        resolve(response.data || null);
                    },
                    error: () => {
                        resolve(null);
                    }
                });
            });
        }

        addCountdownTimer($element, targetDate) {
            const updateTimer = () => {
                const now = new Date();
                const diff = targetDate - now;
                
                if (diff <= 0) {
                    $element.next('.countdown').remove();
                    return;
                }
                
                const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                
                let countdownText = '';
                if (days > 0) {
                    countdownText = `in ${days}d ${hours}h`;
                } else if (hours > 0) {
                    countdownText = `in ${hours}h ${minutes}m`;
                } else {
                    countdownText = `in ${minutes}m`;
                }
                
                let $countdown = $element.next('.countdown');
                if (!$countdown.length) {
                    $countdown = $('<small class="countdown" style="display: block; color: #3b82f6; font-weight: 600;"></small>');
                    $element.after($countdown);
                }
                
                $countdown.text(countdownText);
            };
            
            updateTimer();
            setInterval(updateTimer, 60000); // Update every minute
        }

        setupLazyLoading() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });

                $('img.lazy').each((index, img) => {
                    imageObserver.observe(img);
                });
            }
        }

        setupMatchTimers() {
            $('.match-timer').each((index, timer) => {
                const $timer = $(timer);
                const endTime = new Date($timer.data('end-time')).getTime();
                
                const updateTimer = () => {
                    const now = new Date().getTime();
                    const distance = endTime - now;
                    
                    if (distance < 0) {
                        $timer.text('FT');
                        return;
                    }
                    
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                    
                    $timer.text(`${minutes}:${seconds.toString().padStart(2, '0')}`);
                };
                
                updateTimer();
                setInterval(updateTimer, 1000);
            });
        }

        setupAutoRefresh() {
            // Auto-refresh data blocks with live data
            $('[data-auto-refresh]').each((index, element) => {
                const $element = $(element);
                const interval = parseInt($element.data('refresh-interval')) || 30000;
                
                setInterval(() => {
                    if (this.isElementInViewport(element)) {
                        this.refreshDataBlock($element);
                    }
                }, interval);
            });
        }

        refreshDataBlock($block) {
            // Trigger refresh for Remote Data Blocks
            $block.trigger('remotedatablocks:refresh');
        }

        isElementInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        handleSmoothScroll(e) {
            e.preventDefault();
            
            const target = $(e.target).attr('href');
            if (target && target.startsWith('#')) {
                const $target = $(target);
                if ($target.length) {
                    $('html, body').animate({
                        scrollTop: $target.offset().top - 100
                    }, 500);
                }
            }
        }
    }

    // Global functions for template use
    window.NewspackSports = {
        refreshStandings: function(sport, competition) {
            $(`.sports-data-block[data-sport="${sport}"][data-competition="${competition}"]`).trigger('remotedatablocks:refresh');
        },
        
        switchTab: function(tabName) {
            const $tab = $(`.sports-tab-link[href*="/${tabName}/"]`);
            if ($tab.length) {
                $tab[0].click();
            }
        },
        
        getSportsContext: function() {
            return {
                sport: $('body').hasClass('sport-') ? $('body').attr('class').match(/sport-([^\s]+)/)[1] : null,
                competition: $('body').hasClass('competition-') ? $('body').attr('class').match(/competition-([^\s]+)/)[1] : null,
                tab: $('body').hasClass('sports-tab-') ? $('body').attr('class').match(/sports-tab-([^\s]+)/)[1] : 'news'
            };
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        new NewspackSportsFrontend();
    });

})(jQuery);