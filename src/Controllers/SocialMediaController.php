<?php

/**
 * Social Media Controller
 * Handles social media sharing functionality (NO LOGIN - only content sharing)
 * Supports: Facebook, Twitter, Instagram, LinkedIn
 */
class SocialMediaController {
    private $db;
    private $settings;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        require_once __DIR__ . '/../Models/OrganizationSettings.php';
        $this->settings = new OrganizationSettings($this->db);
    }

    /**
     * Main router for social media actions
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? 'index';

        switch ($action) {
            case 'share':
                $this->generateShareLinks();
                break;
            case 'get_share_url':
                $this->getShareUrl();
                break;
            case 'get_public_config':
                $this->getPublicConfig();
                break;
            default:
                $this->showError('Acción no válida');
        }
    }

    /**
     * Generate share links for a given content
     * Returns JSON with share URLs for enabled platforms
     */
    private function generateShareLinks() {
        header('Content-Type: application/json');

        try {
            $url = $_GET['url'] ?? '';
            $title = $_GET['title'] ?? '';
            $description = $_GET['description'] ?? '';

            if (empty($url)) {
                throw new Exception('URL es requerida');
            }

            $encodedUrl = urlencode($url);
            $encodedTitle = urlencode($title);

            $shareLinks = [];

            // Facebook
            if ($this->settings->get('facebook_enabled') == '1') {
                $facebookAppId = $this->settings->get('facebook_app_id');
                if (!empty($facebookAppId)) {
                    $shareLinks['facebook'] = "https://www.facebook.com/dialog/share?" . http_build_query([
                        'app_id' => $facebookAppId,
                        'href' => $url,
                        'quote' => $title,
                        'display' => 'popup',
                        'redirect_uri' => $url
                    ]);
                } else {
                    $shareLinks['facebook'] = "https://www.facebook.com/sharer/sharer.php?u={$encodedUrl}";
                }
            }

            // Twitter/X
            if ($this->settings->get('twitter_enabled') == '1') {
                $shareLinks['twitter'] = "https://twitter.com/intent/tweet?" . http_build_query([
                    'url' => $url,
                    'text' => $title
                ]);
            }

            // LinkedIn
            if ($this->settings->get('linkedin_enabled') == '1') {
                $shareLinks['linkedin'] = "https://www.linkedin.com/sharing/share-offsite/?" . http_build_query([
                    'url' => $url,
                    'mini' => 'true'
                ]);
            }

            // Instagram
            if ($this->settings->get('instagram_enabled') == '1') {
                $shareLinks['instagram'] = [
                    'type' => 'manual',
                    'message' => 'Instagram no soporta compartir desde web. Comparte manualmente desde la app móvil.',
                    'url' => $url
                ];
            }

            $shareLinks['whatsapp'] = "https://wa.me/?text=" . urlencode($title . " " . $url);
            $shareLinks['telegram'] = "https://t.me/share/url?" . http_build_query(['url' => $url, 'text' => $title]);

            echo json_encode([
                'success' => true,
                'links' => $shareLinks
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get social media configuration (public info only)
     */
    public function getPublicConfig() {
        header('Content-Type: application/json');

        $config = [
            'facebook_enabled' => $this->settings->get('facebook_enabled') == '1',
            'twitter_enabled' => $this->settings->get('twitter_enabled') == '1',
            'linkedin_enabled' => $this->settings->get('linkedin_enabled') == '1',
            'instagram_enabled' => $this->settings->get('instagram_enabled') == '1',
            'site_name' => $this->settings->get('share_site_name'),
            'default_image' => $this->settings->get('share_default_image')
        ];

        echo json_encode($config);
    }

    private function getShareUrl() {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'url' => '']);
    }

    private function showError($message) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
