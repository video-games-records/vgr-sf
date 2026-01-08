<?php

namespace App\SharedKernel\Domain\Security;

/**
 * Interface defining security event types
 */
interface SecurityEventTypeInterface
{
    /**
     * Get the event type code (used in database)
     */
    public function getCode(): string;

    /**
     * Get the event type label (for display)
     */
    public function getLabel(): string;

    /**
     * Get the icon class (FontAwesome)
     */
    public function getIcon(): string;

    /**
     * Get the CSS class (e.g. bootstrap bg-* classes)
     */
    public function getCssClass(): string;

    /**
     * Get the severity level (info, warning, error, critical)
     */
    public function getSeverity(): string;

    /**
     * Check if this event type matches a given code
     */
    public function is(string $code): bool;
}
