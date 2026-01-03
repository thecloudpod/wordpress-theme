/**
 * The Cloud Pod Audio Player
 * Handles main player and miniplayer functionality
 */

(function() {
    'use strict';

    // State
    let currentAudio = null;
    let isPlaying = false;
    let miniplayerActive = false;

    // Main player elements
    const mainAudio = document.getElementById('main-audio');
    const playPauseBtn = document.getElementById('play-pause-btn');
    const playIcon = document.getElementById('play-icon');
    const rewindBtn = document.getElementById('rewind-btn');
    const forwardBtn = document.getElementById('forward-btn');
    const speedControl = document.getElementById('playback-speed');
    const progressBar = document.getElementById('progress-bar');
    const progressFill = document.getElementById('progress-fill');
    const currentTimeEl = document.getElementById('current-time');
    const totalDurationEl = document.getElementById('total-duration');

    // Miniplayer elements
    const miniplayer = document.getElementById('miniplayer');
    const miniPlayPauseBtn = document.getElementById('mini-play-pause');
    const miniPlayIcon = document.getElementById('mini-play-icon');
    const miniProgressBar = document.getElementById('mini-progress-bar');
    const miniProgressFill = document.getElementById('mini-progress-fill');
    const miniCurrentTime = document.getElementById('mini-current-time');
    const miniDuration = document.getElementById('mini-duration');
    const miniArtwork = document.getElementById('mini-artwork');
    const miniTitle = document.getElementById('mini-title');
    const miniMeta = document.getElementById('mini-meta');
    const miniClose = document.getElementById('mini-close');

    /**
     * Initialize player
     */
    function init() {
        if (!mainAudio) return;

        currentAudio = mainAudio;
        setupEventListeners();
        setupScrollBehavior();
    }

    /**
     * Setup event listeners for main player
     */
    function setupEventListeners() {
        // Play/Pause
        if (playPauseBtn) {
            playPauseBtn.addEventListener('click', togglePlay);
        }

        // Rewind/Forward
        if (rewindBtn) {
            rewindBtn.addEventListener('click', () => skip(-10));
        }
        if (forwardBtn) {
            forwardBtn.addEventListener('click', () => skip(30));
        }

        // Progress bar
        if (progressBar) {
            progressBar.addEventListener('click', seek);
        }

        // Speed control
        if (speedControl) {
            speedControl.addEventListener('change', function() {
                currentAudio.playbackRate = parseFloat(this.value);
            });
        }

        // Audio events
        currentAudio.addEventListener('loadedmetadata', updateDuration);
        currentAudio.addEventListener('timeupdate', updateProgress);
        currentAudio.addEventListener('ended', handleEnded);

        // Miniplayer controls
        if (miniPlayPauseBtn) {
            miniPlayPauseBtn.addEventListener('click', togglePlay);
        }
        if (miniProgressBar) {
            miniProgressBar.addEventListener('click', seekMini);
        }
        if (miniClose) {
            miniClose.addEventListener('click', closeMiniPlayer);
        }
    }

    /**
     * Setup scroll behavior for miniplayer
     */
    function setupScrollBehavior() {
        let playerObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                // Show miniplayer when main player is out of view and audio is playing
                if (!entry.isIntersecting && isPlaying) {
                    showMiniPlayer();
                } else if (entry.isIntersecting) {
                    hideMiniPlayer();
                }
            });
        }, {
            threshold: 0.1
        });

        const playerElement = document.querySelector('.podcast-player');
        if (playerElement) {
            playerObserver.observe(playerElement);
        }
    }

    /**
     * Toggle play/pause
     */
    function togglePlay() {
        if (!currentAudio) return;

        if (isPlaying) {
            currentAudio.pause();
            isPlaying = false;
            updatePlayIcon('▶');
        } else {
            currentAudio.play();
            isPlaying = true;
            updatePlayIcon('⏸');
        }
    }

    /**
     * Update play icon
     */
    function updatePlayIcon(icon) {
        if (playIcon) playIcon.textContent = icon;
        if (miniPlayIcon) miniPlayIcon.textContent = icon;
    }

    /**
     * Skip forward/backward
     */
    function skip(seconds) {
        if (!currentAudio) return;
        currentAudio.currentTime += seconds;
    }

    /**
     * Seek to position (main player)
     */
    function seek(e) {
        if (!currentAudio || !progressBar) return;
        
        const rect = progressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        currentAudio.currentTime = pos * currentAudio.duration;
    }

    /**
     * Seek to position (miniplayer)
     */
    function seekMini(e) {
        if (!currentAudio || !miniProgressBar) return;
        
        const rect = miniProgressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        currentAudio.currentTime = pos * currentAudio.duration;
    }

    /**
     * Update duration display
     */
    function updateDuration() {
        if (!currentAudio) return;
        
        const duration = formatTime(currentAudio.duration);
        if (totalDurationEl) totalDurationEl.textContent = duration;
        if (miniDuration) miniDuration.textContent = duration;
    }

    /**
     * Update progress bar and time
     */
    function updateProgress() {
        if (!currentAudio) return;

        const percent = (currentAudio.currentTime / currentAudio.duration) * 100;
        const currentTime = formatTime(currentAudio.currentTime);

        // Update main player
        if (progressFill) progressFill.style.width = percent + '%';
        if (currentTimeEl) currentTimeEl.textContent = currentTime;

        // Update miniplayer
        if (miniProgressFill) miniProgressFill.style.width = percent + '%';
        if (miniCurrentTime) miniCurrentTime.textContent = currentTime;
    }

    /**
     * Handle audio ended
     */
    function handleEnded() {
        isPlaying = false;
        updatePlayIcon('▶');
        hideMiniPlayer();
    }

    /**
     * Show miniplayer
     */
    function showMiniPlayer() {
        if (!miniplayer || miniplayerActive) return;

        // Populate miniplayer info
        const playerArtwork = document.querySelector('.player-artwork');
        const playerTitle = document.querySelector('.player-info h3');
        const episodeDate = document.querySelector('.player-info .podcast-meta span:first-child');

        if (miniArtwork && playerArtwork) {
            miniArtwork.src = playerArtwork.src;
            miniArtwork.alt = playerArtwork.alt || 'Episode artwork';
        }

        if (miniTitle && playerTitle) {
            miniTitle.textContent = playerTitle.textContent;
        }

        if (miniMeta && episodeDate) {
            miniMeta.textContent = episodeDate.textContent;
        }

        miniplayer.classList.add('active');
        miniplayerActive = true;

        // Add padding to body to prevent content from being hidden
        document.body.style.paddingBottom = '90px';
    }

    /**
     * Hide miniplayer
     */
    function hideMiniPlayer() {
        if (!miniplayer || !miniplayerActive) return;

        miniplayer.classList.remove('active');
        miniplayerActive = false;
        document.body.style.paddingBottom = '0';
    }

    /**
     * Close miniplayer
     */
    function closeMiniPlayer() {
        hideMiniPlayer();
        if (currentAudio && isPlaying) {
            togglePlay();
        }
    }

    /**
     * Format time in MM:SS or HH:MM:SS
     */
    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';

        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);

        if (hours > 0) {
            return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        } else {
            return `${minutes}:${String(secs).padStart(2, '0')}`;
        }
    }

    /**
     * Keyboard shortcuts
     */
    document.addEventListener('keydown', (e) => {
        if (!currentAudio) return;
        
        // Don't trigger if user is typing in an input
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        switch(e.key) {
            case ' ':
                e.preventDefault();
                togglePlay();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                skip(-10);
                break;
            case 'ArrowRight':
                e.preventDefault();
                skip(30);
                break;
        }
    });

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
