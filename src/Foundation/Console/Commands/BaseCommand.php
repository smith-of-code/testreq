<?php
/**
 * Created by PhpStorm.
 * User: vyfvfv
 * Date: 05.08.15
 * Time: 20:01
 */

namespace QSoft\Foundation\Console\Commands;


use Fifa2018\Database\ConnectionResolver;
use Fifa2018\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Console\OutputStyle;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class BaseCommand extends Command
{

    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var InputInterface
     */
    protected $input;

    protected function getMigrationPath() {
        return QSOFT_CORE_ROOT.'local/src/Migrate';
    }

    protected function call($command, $options = []) {

        $input = new ArrayInput(array_merge(['command' => $command], $options));
        $command = $this->getApplication()->find($command);
        $command->run($input, $this->output);

    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = new OutputStyle($input, $output);
        $this->input = $input;
        return $this->fire();
    }

    protected function ask($question, $default = null, $require = false) {

        $questionHelper = $this->getHelper('question');
        $answer = null;

        $question = new Question(($default ? $question.'['.$default.']' : $question).': ', $default);

        while (!$answer) {
            $answer = $questionHelper->ask($this->input, $this->output, $question);
            if (false === $require) {
                break;
            }
        }

        return $answer;

    }

    abstract function fire();


}