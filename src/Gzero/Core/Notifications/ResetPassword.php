<?php

namespace Gzero\Core\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as IlluminateResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends IlluminateResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable Notifiable entity
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line(trans('gzero-core::emails.reset_password.line_1'))
            ->action(trans('gzero-core::emails.reset_password.action'), url('password/reset', $this->token))
            ->line(trans('gzero-core::emails.reset_password.line_2'));
    }
}
