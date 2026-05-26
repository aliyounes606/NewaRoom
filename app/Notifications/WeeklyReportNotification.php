<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Weekly article report notification sent to admin users.
 *
 * Contains a summary of articles published during the past week.
 * Dispatched by the GenerateWeeklyReport job on the `reports` queue.
 *
 * Queue-friendly: implements ShouldQueue.
 */
class WeeklyReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  array{
     *     total_published: int,
     *     total_draft: int,
     *     top_writers: array,
     *     period_start: string,
     *     period_end: string,
     * }  $reportData
     */
    public function __construct(
        public readonly array $reportData,
    ) {
        $this->onQueue('reports');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $periodStart = $this->reportData['period_start'];
        $periodEnd   = $this->reportData['period_end'];

        $mail = (new MailMessage)
            ->subject("NewsRoom Weekly Report — {$periodStart} to {$periodEnd}")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Here is your weekly article report for **{$periodStart}** to **{$periodEnd}**.")
            ->line("---")
            ->line("**Published Articles:** {$this->reportData['total_published']}")
            ->line("**Draft Articles:** {$this->reportData['total_draft']}");

        if (! empty($this->reportData['top_writers'])) {
            $mail->line("---");
            $mail->line("**Top Writers This Week:**");

            foreach ($this->reportData['top_writers'] as $writer) {
                $mail->line("• {$writer['name']} — {$writer['articles_count']} article(s)");
            }
        }

        return $mail->line('Thank you for keeping NewsRoom running!');
    }

    /**
     * Get the array representation of the notification (database channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'type'            => 'weekly_report',
            'period_start'    => $this->reportData['period_start'],
            'period_end'      => $this->reportData['period_end'],
            'total_published' => $this->reportData['total_published'],
            'total_draft'     => $this->reportData['total_draft'],
            'top_writers'     => $this->reportData['top_writers'],
            'message'         => sprintf(
                'Weekly report: %d published, %d draft (%s – %s)',
                $this->reportData['total_published'],
                $this->reportData['total_draft'],
                $this->reportData['period_start'],
                $this->reportData['period_end'],
            ),
        ];
    }
}
