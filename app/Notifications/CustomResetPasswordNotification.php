<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;
    protected $isNewUser;

    public function __construct($token, $isNewUser = false)
    {
        $this->token = $token;
        $this->isNewUser = $isNewUser;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $subject = $this->isNewUser ? 'Set Up Your Hay CIS Account' : 'Reset Your Hay CIS Password';
        $intro = $this->isNewUser
            ? 'An account has been created for you on the Hay Contract Information System. Click the button below to verify your email and set your password.'
            : Lang::get('You are receiving this email because we received a password reset request for your account.');

        return (new MailMessage)
            ->subject($subject)
            ->line($intro)
            ->action($this->isNewUser ? 'Set Password' : Lang::get('Reset Password'), $url)
            ->line(Lang::get('This link will expire in :count minutes.', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
            ->line(Lang::get('If you did not request this, no further action is required.'));
    }
}