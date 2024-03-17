<?php

class View
{
    protected $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    public function render(array $data): string
    {
        if (!is_readable($this->filePath)) {
            throw new RuntimeException(sprintf('The view at path "%1$s" does not exist or is not readable', $this->filePath));
        }

        ob_start();
        extract($data);
        include $this->filePath;
        return ob_get_clean();
    }
}