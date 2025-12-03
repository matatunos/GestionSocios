/**
 * Social Media Share Library
 * Handles sharing content to various social media platforms
 */
class SocialMediaShare {
    constructor(config = {}) {
        this.config = {
            url: config.url || window.location.href,
            title: config.title || document.title,
            description: config.description || '',
            image: config.image || ''
        };
        this.platforms = {
            facebook: {
                icon: 'fab fa-facebook-f',
                label: 'Facebook',
                color: '#1877f2',
                enabled: false
            },
            twitter: {
                icon: 'fab fa-twitter',
                label: 'Twitter',
                color: '#1da1f2',
                enabled: false
            },
            linkedin: {
                icon: 'fab fa-linkedin-in',
                label: 'LinkedIn',
                color: '#0077b5',
                enabled: false
            },
            whatsapp: {
                icon: 'fab fa-whatsapp',
                label: 'WhatsApp',
                color: '#25d366',
                enabled: true
            },
            telegram: {
                icon: 'fab fa-telegram-plane',
                label: 'Telegram',
                color: '#0088cc',
                enabled: true
            },
            copy: {
                icon: 'fas fa-link',
                label: 'Copiar enlace',
                color: '#6c757d',
                enabled: true
            }
        };
        this.loadConfig();
    }

    /**
     * Load social media configuration from server
     */
    async loadConfig() {
        try {
            const response = await fetch('index.php?page=social_media&action=get_public_config');
            if (response.ok) {
                const config = await response.json();
                this.platforms.facebook.enabled = config.facebook_enabled || false;
                this.platforms.twitter.enabled = config.twitter_enabled || false;
                this.platforms.linkedin.enabled = config.linkedin_enabled || false;
            }
        } catch (error) {
            console.error('Error loading social media config:', error);
        }
    }

    /**
     * Initialize share buttons in a container
     */
    async initButtons(containerSelector, options = {}) {
        const container = document.querySelector(containerSelector);
        if (!container) {
            console.error('Container not found:', containerSelector);
            return;
        }

        // Wait for config to load
        await this.loadConfig();

        const {
            platforms = ['facebook', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'copy'],
            size = 'medium',
            showLabels = true,
            style = 'default'
        } = options;

        // Clear container
        container.innerHTML = '';

        // Create button container
        const buttonsWrapper = document.createElement('div');
        buttonsWrapper.className = `social-share-buttons social-share-${size} social-share-${style}`;

        platforms.forEach(platform => {
            if (this.platforms[platform] && this.platforms[platform].enabled) {
                const button = this.createButton(platform, showLabels, size);
                buttonsWrapper.appendChild(button);
            }
        });

        container.appendChild(buttonsWrapper);
    }

    /**
     * Create a share button for a platform
     */
    createButton(platform, showLabel = true, size = 'medium') {
        const platformData = this.platforms[platform];
        const button = document.createElement('button');
        button.className = `social-share-btn social-share-${platform}`;
        button.setAttribute('data-platform', platform);
        button.style.backgroundColor = platformData.color;
        button.title = `Compartir en ${platformData.label}`;

        const icon = document.createElement('i');
        icon.className = platformData.icon;
        button.appendChild(icon);

        if (showLabel) {
            const label = document.createElement('span');
            label.textContent = platformData.label;
            button.appendChild(label);
        }

        button.addEventListener('click', (e) => {
            e.preventDefault();
            this.share(platform);
        });

        return button;
    }

    /**
     * Share to a specific platform
     */
    async share(platform) {
        try {
            const response = await fetch(
                `index.php?page=social_media&action=share&url=${encodeURIComponent(this.config.url)}&title=${encodeURIComponent(this.config.title)}&description=${encodeURIComponent(this.config.description)}`
            );
            
            if (!response.ok) throw new Error('Failed to generate share link');
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Error generating share link');
            }

            const shareLink = data.links[platform];

            if (platform === 'copy') {
                this.copyToClipboard(this.config.url);
                return;
            }

            if (shareLink) {
                if (typeof shareLink === 'object' && shareLink.type === 'manual') {
                    alert(shareLink.message);
                } else {
                    this.openShareWindow(shareLink);
                }
            }
        } catch (error) {
            console.error('Error sharing:', error);
            alert('Error al compartir. Por favor, intenta nuevamente.');
        }
    }

    /**
     * Open share window
     */
    openShareWindow(url) {
        const width = 600;
        const height = 500;
        const left = (window.innerWidth - width) / 2;
        const top = (window.innerHeight - height) / 2;
        
        window.open(
            url,
            'share',
            `width=${width},height=${height},left=${left},top=${top},toolbar=0,location=0,menubar=0`
        );
    }

    /**
     * Copy URL to clipboard
     */
    async copyToClipboard(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback for older browsers
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.left = '-999999px';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
            
            // Show success message
            this.showToast('Enlace copiado al portapapeles', 'success');
        } catch (error) {
            console.error('Error copying to clipboard:', error);
            this.showToast('Error al copiar el enlace', 'error');
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `social-share-toast social-share-toast-${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }
}

// Make it globally available
window.SocialMediaShare = SocialMediaShare;
