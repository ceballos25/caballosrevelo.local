<?php
declare(strict_types=1);

namespace App\Domain\Ticket\ValueObject;

final class TicketStatus
{
    public const AVAILABLE = 0;
    public const SOLD = 1;
    public const RESERVED = 2;

    private function __construct()
    {
    }
}
