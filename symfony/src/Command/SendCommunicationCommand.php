<?php

namespace App\Command;

use App\Base\BaseCommand;
use App\Communication\Sender;
use App\Entity\Communication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendCommunicationCommand extends BaseCommand
{
    const PAUSE = 500000; // 2 sms / second

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('send:communication')
            ->setDescription('Run a "send:message" process on all messages of the given communication')
            ->addArgument('communication-id', InputArgument::REQUIRED, 'Communication ID');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $communicationId = $input->getArgument('communication-id');

        /* @var Communication $communication */
        $communication = $this->getManager(Communication::class)->find($communicationId);

        if (!$communication) {
            $output->writeln(sprintf('<error>Communication "%d" not found.</error>', $communicationId));

            return 1;
        }

        date_default_timezone_set('Europe/Paris');

        foreach ($communication->getMessages() as $message) {
            if (!$message->getMessageId()) {
                $this->get(Sender::class)->send($message);
                usleep(self::PAUSE);
            }
        }
    }
}
