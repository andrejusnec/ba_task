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
            new TwigFunction('status_style', [$this, 'addStyleOnStatus'])
        ];
    }

    /**
     * @param $status
     * Transforms the status boolean to a string representation for a user
     * @return string
     */
    public function formatStatus($status): string
    {
        if (null === $status) {
            return 'Sended';
        }
        return $status ? 'Accepted' : 'Rejected';
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
            $styleText = 'style=background-color:lightgreen;pointer-events:none';
        }
        if (false === $sendStatus || false === $receiveStatus) {
            $styleText = 'style=background-color:lightgrey;pointer-events:none';
        }
        return $styleText;
    }

}