<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-10-20 14:32:27 +0800
 */

namespace Teddy\Abstracts;

use Exception;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends SymfonyCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var SymfonyStyle
     */
    protected $output;

    /**
     * @var int
     */
    protected $verbosity = OutputInterface::VERBOSITY_NORMAL;

    /**
     * @var array
     */
    protected $verbosityMap = [
        'v'      => OutputInterface::VERBOSITY_VERBOSE,
        'vv'     => OutputInterface::VERBOSITY_VERY_VERBOSE,
        'vvv'    => OutputInterface::VERBOSITY_DEBUG,
        'quiet'  => OutputInterface::VERBOSITY_QUIET,
        'normal' => OutputInterface::VERBOSITY_NORMAL,
    ];

    public function run(InputInterface $input, OutputInterface $output): int
    {
        return parent::run(
            $this->input  = $input,
            $this->output = new SymfonyStyle($input, $output)
        );
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
        $styled = $style ? "<{$style}>{$string}</{$style}>" : $string;
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
        if (!$this->output->getFormatter()->hasStyle('warning')) {
            $style = new OutputFormatterStyle('yellow');
            $this->output->getFormatter()->setStyle('warning', $style);
        }

        $this->line($string, 'warning', $verbosity);
    }

    public function alert(string $string): void
    {
        $length = mb_strlen(strip_tags($string)) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     '.$string.'     *');
        $this->comment(str_repeat('*', $length));

        $this->output->newLine();
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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->handle();
        } catch (Exception $e) {
            /**
             * @var SymfonyStyle $output
             */
            $output->error($e->getMessage());

            return 255;
        }

        return 0;
    }

    abstract protected function handle();
}
