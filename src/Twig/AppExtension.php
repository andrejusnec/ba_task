<?php

namespace App\Twig;

use App\Services\UploaderHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private UploaderHelper $uploaderHelper;

    public function __construct(UploaderHelper $uploaderHelper)
    {
        $this->uploaderHelper = $uploaderHelper;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('status_text', [$this, 'formatStatus']),
            new TwigFunction('status_style', [$this, 'addStyleOnStatus']),
            new TwigFunction('uploaded_asset', [$this, 'getUploadedAssetPath'])
        ];
    }

    /**
     * @param $receiveStatus
     * @param $sendStatus
     * @return string
     */
    public function formatStatus($receiveStatus, $sendStatus): string
    {
        if (!$sendStatus) {
            return 'Canceled';
        }
        if (null === $receiveStatus && true == $sendStatus) {
            return 'Sended';
        }

        return $receiveStatus ? 'Accepted' : 'Rejected';
    }

    /**
     * @param $receiveStatus
     * @param $sendStatus
     * Adds css styling based on status and disables element
     * @return string
     */
    public function addStyleOnStatus($receiveStatus, $sendStatus): string
    {
        $styleText = '';
        if (true === $sendStatus && true === $receiveStatus) {
            $styleText = 'style=background-color:lightgreen;pointer-events:none class=active-ql';
        }
        if (false === $sendStatus || false === $receiveStatus) {
            $styleText = 'style=background-color:lightgrey;pointer-events:none class=inactive-ql';
        }

        return $styleText;
    }

    /**
     * @param string $path
     * @return string
     */
    public function getUploadedAssetPath(string $path): string
    {
        return $this->uploaderHelper->getPublicPath($path);
    }
}
