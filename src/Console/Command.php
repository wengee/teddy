<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-12 15:36:16 +0800
 */

namespace Teddy\Console;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class Command extends SymfonyCommand
{
    use Macroable;

    protected $input;

    protected $output;

    protected $name;

    protected $signature;

    protected $description = '';

    protected $hidden = false;

    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    protected $verbosityMap = [
        'v'         => OutputInterface::VERBOSITY_VERBOSE,
        'vv'        => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv'       => OutputInterface::VERBOSITY_DEBUG,
        'quiet'     => OutputInterface::VERBOSITY_QUIET,
        'normal'    => OutputInterface::VERBOSITY_NORMAL,
    ];

    public function __construct()
    {
        if (isset($this->signature)) {
            $this->configureUsingFluentDefinition();
        } else {
            parent::__construct($this->name);
        }

        $this->setDescription($this->description);
        $this->setHidden($this->hidden);

        if (! isset($this->signature)) {
            $this->specifyParameters();
        }
    }

    public function hasArgument(string $name): bool
    {
        return $this->input->hasArgument($name);
    }

    public function argument(?string $key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    public function arguments(): array
    {
        return $this->argument();
    }

    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }

    public function option(?string $key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    public function options(): array
    {
        return $this->option();
    }

    public function confirm(string $question, bool $default = false)
    {
        return $this->output->confirm($question, $default);
    }

    public function ask(string $question, bool $default = null)
    {
        return $this->output->ask($question, $default);
    }

    public function anticipate(string $question, array $choices, ?string $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    public function askWithCompletion(string $question, array $choices, ?string $default = null)
    {
        $question = new Question($question, $default);
        $question->setAutocompleterValues($choices);
        return $this->output->askQuestion($question);
    }

    public function secret(string $question, bool $fallback = true)
    {
        $question = new Question($question);
        $question->setHidden(true)->setHiddenFallback($fallback);
        return $this->output->askQuestion($question);
    }

    public function choice(string $question, array $choices, ?string $default = null, $attempts = null, ?bool $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);
        $question->setMaxAttempts($attempts)->setMultiselect($multiple);
        return $this->output->askQuestion($question);
    }

    public function table(array $headers, $rows, string $tableStyle = 'default', array $columnStyles = []): void
    {
        $table = new Table($this->output);

        $rows = (array) $rows;
        $table->setHeaders((array) $headers)->setRows($rows)->setStyle($tableStyle);

        foreach ($columnStyles as $columnIndex => $columnStyle) {
            $table->setColumnStyle($columnIndex, $columnStyle);
        }

        $table->render();
    }

    public function info(string $string, $verbosity = null): void
    {
        $this->line($string, 'info', $verbosity);
    }

    public function line(string $string, ?string $style = null, $verbosity = null): void
    {
        $styled = $style ? "<$style>$string</$style>" : $string;
        $this->output->writeln($styled, $this->parseVerbosity($verbosity));
    }

    public function comment(string $string, $verbosity = null): void
    {
        $this->line($string, 'comment', $verbosity);
    }

    public function question(string $string, $verbosity = null): void
    {
        $this->line($string, 'question', $verbosity);
    }

    public function error(string $string, $verbosity = null): void
    {
        $this->line($string, 'error', $verbosity);
    }

    public function warn(string $string, $verbosity = null): void
    {
        if (! $this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    public function alert(string $string): void
    {
        $length = Str::length(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', $length));

        $this->output->newLine();
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        return parent::run(
            $this->input = $input,
            $this->output = new SymfonyStyle($input, $output)
        );
    }

    abstract protected function handle();

    protected function configureUsingFluentDefinition(): void
    {
        list($name, $arguments, $options) = Parser::parse($this->signature);

        parent::__construct($this->name = $name);
        $this->getDefinition()->addArguments($arguments);
        $this->getDefinition()->addOptions($options);
    }

    protected function specifyParameters(): void
    {
        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }

    protected function getArguments(): array
    {
        return [];
    }

    protected function getOptions(): array
    {
        return [];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->handle();
        } catch (Exception $e) {
            $output->error($e->getMessage());
            return 255;
        }

        return 0;
    }

    protected function setVerbosity($level): void
    {
        $this->verbosity = $this->parseVerbosity($level);
    }

    protected function parseVerbosity($level = null)
    {
        if (isset($this->verbosityMap[$level])) {
            $level = $this->verbosityMap[$level];
        } elseif (!is_int($level)) {
            $level = $this->verbosity;
        }

        return $level;
    }
}
