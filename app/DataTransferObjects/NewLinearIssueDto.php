<?php

namespace App\DataTransferObjects;

class NewLinearIssueDto
{
    public function __construct(
        public string $title,
        public string $teamId,
        public string $description = '',
    )
    {
    }
}
