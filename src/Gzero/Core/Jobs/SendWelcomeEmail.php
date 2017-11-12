<?php namespace Gzero\Core\Jobs;

use Gzero\Core\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendWelcomeEmail implements ShouldQueue {
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var User */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user User
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer $mailer Mailer instance
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $mailer->send(
            'gzero-base::emails.auth.welcome',
            ['user' => $this->user],
            function ($m) {
                $m->to($this->user->email, $this->user->getPresenter()->displayName())
                    ->subject(trans('emails.welcome.subject', ['siteName' => config('app.name')]));
            }
        );
    }
}
