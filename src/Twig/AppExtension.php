<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('status_text', [$this, 'formatStatus']),
            new TwigFunction('status_style', [$this, 'addStyleOnStatus']),
        ];
    }

    /**
     * @param $receiveStatus
     * @param $sendStatus
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
     */
    public function addStyleOnStatus($receiveStatus, $sendStatus): string
    {
        $styleText = '';
        if (true === $sendStatus && true === $receiveStatus) {
            $styleText = 'style=background-color:lightgreen;pointer-events:none';
        }
        if (false === $sendStatus || false === $receiveStatus) {
            $styleText = 'style=background-color:lightgrey;pointer-events:none';
        }

        return $styleText;
    }
}
