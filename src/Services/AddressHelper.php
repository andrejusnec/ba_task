<?php

namespace App\Services;

class AddressHelper
{
    /**
     * @param array $queryLists
     * Checks if the current AddressBook entry is in active share state
     * Current service has been created as an example, although it is only used once
     * @return bool
     */
    public function checkForActiveQueryLists(array $queryLists): bool
    {
        $canDelete = true;
        foreach($queryLists as $item) {
                if(true === $item->getSendStatus() && null === $item->getReceiveStatus()) {
                    $canDelete = false;
                    break;
                }
            }
        return $canDelete;
    }

}