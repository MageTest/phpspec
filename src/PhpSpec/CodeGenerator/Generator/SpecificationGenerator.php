<?php

namespace PhpSpec\CodeGenerator\Generator;

use PhpSpec\Console\IO;
use PhpSpec\CodeGenerator\TemplateRenderer;
use PhpSpec\Util\Filesystem;
use PhpSpec\Locator\ResourceInterface;

class SpecificationGenerator implements GeneratorInterface
{
    private $io;
    private $templates;
    private $filesystem;

    public function __construct(IO $io, TemplateRenderer $templates, Filesystem $filesystem = null)
    {
        $this->io         = $io;
        $this->templates  = $templates;
        $this->filesystem = $filesystem ?: new Filesystem;
    }

    public function supports(ResourceInterface $resource, $generation, array $data)
    {
        return 'specification' === $generation;
    }

    public function generate(ResourceInterface $resource, array $data = array())
    {
        $filepath = $resource->getSpecFilename();
        if ($this->filesystem->pathExists($filepath)) {
            $message = sprintf('File "%s" already exists. Overwrite?', basename($filepath));
            if (!$this->io->askConfirmation($message, false)) {
                return;
            }

            $this->io->writeln();
        }

        $path = dirname($filepath);
        if (!$this->filesystem->isDirectory($path)) {
            $this->filesystem->makeDirectory($path);
        }

        $content = $this->renderTemplate($resource, $filepath);

        $this->filesystem->putFileContents($filepath, $content);
        $this->io->writeln(sprintf(
            "<info>Specification for <value>%s</value> created in <value>%s</value>.</info>\n",
            $resource->getSrcClassname(), $filepath
        ));
    }

    public function getPriority()
    {
        return 0;
    }

    protected function renderTemplate(ResourceInterface $resource, $filepath)
    {
        $values = array(
            '%filepath%'  => $filepath,
            '%name%'      => $resource->getSpecName(),
            '%namespace%' => $resource->getSpecNamespace(),
            '%subject%'   => $resource->getSrcClassname()
        );

        if (!$content = $this->templates->render('specification', $values)) {
            $content = $this->templates->renderString($this->getTemplate(), $values);
        }

        return $content;
    }

    protected function getTemplate()
    {
        return file_get_contents(__FILE__, null, null, __COMPILER_HALT_OFFSET__);
    }
}
__halt_compiler();<?php

namespace %namespace%;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class %name% extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('%subject%');
    }
}
