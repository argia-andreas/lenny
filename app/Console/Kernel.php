<?php

declare(strict_types=1);


namespace App\Console;

use LaravelZero\Framework\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    protected $input = null;


    /**
     * {@inheritdoc}
     */
    public function handle($input, $output = null)
    {
        $this->input = $input;

        return parent::handle($input, $output);
    }


    /**
     * {@inheritdoc}
     */
    public function bootstrap(): void
    {
        parent::bootstrap();
    }
}
