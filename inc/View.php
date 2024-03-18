<?php
/**
 * Represents a view in the MVC architecture. This class is responsible for rendering HTML templates.
 */
class View
{
    /**
     * Path to the file that will be rendered.
     *
     * @var string
     */
    protected $filePath;

    /**
     * Initializes the view with a file path.
     *
     * @param string $filePath The path to the view file.
     */
    public function __construct(string $filePath)
    {
        // Store the path to the view file.
        $this->filePath = $filePath;
    }

    /**
     * Renders the view file and returns its content as a string.
     *
     * This method uses output buffering to capture the included file's output
     * and returns it as a string. This allows for flexible view composition and embedding.
     *
     * @param array $data Associative array of data to be made available to the view file.
     * @return string The rendered content of the view file.
     * @throws RuntimeException If the view file doesn't exist or isn't readable.
     */
    public function render(array $data): string
    {
        // Check if the view file is readable.
        if (!is_readable($this->filePath)) {
            // Throw an exception if the file can't be read. This could be due to file permissions or the file not existing.
            throw new RuntimeException(sprintf('The view at path "%1$s" does not exist or is not readable', $this->filePath));
        }

        // Start output buffering. This will capture all output generated by the include statement below.
        ob_start();

        // Extract the data array to variables. This makes each key in the $data array accessible as a variable
        // within the view file, simplifying the access to data passed to the view.
        extract($data);

        // Include the view file. Its output will be captured by the output buffering started earlier.
        include $this->filePath;

        // Clean (erase) the output buffer and return its contents.
        return ob_get_clean();
    }
}
