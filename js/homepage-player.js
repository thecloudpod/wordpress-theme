/**
 * Homepage Miniplayer
 * Controls the sticky player on the homepage
 */

(function() {
    'use strict';

    const homepageAudio = document.getElementById('homepage-audio');
    const playBtn = document.getElementById('homepage-play-btn');
    const playIcon = document.getElementById('homepage-play-icon');
    const rewindBtn = document.getElementById('homepage-rewind-btn');
    const forwardBtn = document.getElementById('homepage-forward-btn');
    const volumeBtn = document.getElementById('homepage-volume-btn');
    const volumeSlider = document.getElementById('homepage-volume');
    const progressBar = document.getElementById('homepage-progress');
    const progressFill = document.getElementById('homepage-fill');
    const currentTimeEl = document.getElementById('homepage-current');
    const durationEl = document.getElementById('homepage-duration');

    if (!homepageAudio) return;

    let isPlaying = false;

    // Play/Pause
    playBtn.addEventListener('click', function() {
        if (isPlaying) {
            homepageAudio.pause();
            isPlaying = false;
            playIcon.textContent = '▶';
        } else {
            homepageAudio.play();
            isPlaying = true;
            playIcon.textContent = '⏸';
        }
    });

    // Update duration
    homepageAudio.addEventListener('loadedmetadata', function() {
        durationEl.textContent = formatTime(homepageAudio.duration);
    });

    // Update progress
    homepageAudio.addEventListener('timeupdate', function() {
        const percent = (homepageAudio.currentTime / homepageAudio.duration) * 100;
        progressFill.style.width = percent + '%';
        currentTimeEl.textContent = formatTime(homepageAudio.currentTime);
    });

    // Click to seek
    progressBar.addEventListener('click', function(e) {
        const rect = progressBar.getBoundingClientRect();
        const pos = (e.clientX - rect.left) / rect.width;
        homepageAudio.currentTime = pos * homepageAudio.duration;
    });

    // Handle end
    homepageAudio.addEventListener('ended', function() {
        isPlaying = false;
        playIcon.textContent = '▶';
    });

    // Rewind 10 seconds
    if (rewindBtn) {
        rewindBtn.addEventListener('click', function() {
            homepageAudio.currentTime = Math.max(0, homepageAudio.currentTime - 10);
        });
    }

    // Forward 30 seconds
    if (forwardBtn) {
        forwardBtn.addEventListener('click', function() {
            homepageAudio.currentTime = Math.min(homepageAudio.duration, homepageAudio.currentTime + 30);
        });
    }

    // Volume control
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            homepageAudio.volume = this.value / 100;
            updateVolumeIcon(this.value);
        });
    }

    // Volume button (mute/unmute)
    if (volumeBtn) {
        volumeBtn.addEventListener('click', function() {
            if (homepageAudio.volume > 0) {
                homepageAudio.volume = 0;
                if (volumeSlider) volumeSlider.value = 0;
                updateVolumeIcon(0);
            } else {
                homepageAudio.volume = 1;
                if (volumeSlider) volumeSlider.value = 100;
                updateVolumeIcon(100);
            }
        });
    }

    function formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${String(secs).padStart(2, '0')}`;
    }

    function updateVolumeIcon(volume) {
        if (!volumeBtn) return;
        if (volume == 0) {
            volumeBtn.textContent = '🔇';
        } else if (volume < 50) {
            volumeBtn.textContent = '🔉';
        } else {
            volumeBtn.textContent = '🔊';
        }
    }

})();
