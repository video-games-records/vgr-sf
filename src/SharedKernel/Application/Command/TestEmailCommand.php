<?php

declare(strict_types=1);

namespace App\SharedKernel\Application\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email sending'
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient email address')
            ->addArgument('subject', InputArgument::OPTIONAL, 'Email subject', 'Test Email from VGR')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $to = $input->getArgument('to');
        $subject = $input->getArgument('subject');

        $io->title('Testing email sending');

        try {
            $email = (new Email())
                ->to($to)
                ->subject($subject)
                ->text('This is a test email from VGR.')
                ->html('<h1>Test Email</h1><p>This is a test email from <strong>VGR</strong>.</p>');

            $io->section('Sending email...');
            $io->text(sprintf('To: %s', $to));
            $io->text(sprintf('Subject: %s', $subject));

            $this->mailer->send($email);

            $io->success(sprintf('Email sent successfully to %s', $to));
            $io->note('Check your Brevo dashboard logs to confirm delivery');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Failed to send email: %s', $e->getMessage()));
            $io->text('Error details: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
