<?php

if (!function_exists('getInitials')) {
    function getInitials($name)
    {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $w) {
            $initials .= strtoupper(substr($w, 0, 1));
            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials ?: 'PIC';
    }
}