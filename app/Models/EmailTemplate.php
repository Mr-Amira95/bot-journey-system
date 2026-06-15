<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $fillable = ['type', 'subject', 'body'];

    public static function forType(string $type): self
    {
        return self::firstOrCreate(
            ['type' => $type],
            [
                'subject' => self::defaultSubject($type),
                'body'    => self::defaultBody($type),
            ]
        );
    }

    private static function defaultSubject(string $type): string
    {
        return match($type) {
            'job_offer' => 'Job Offer — {{position}} at {{company}}',
            'contract'  => 'Employment Contract — {{position}}',
            'nda'       => 'Non-Disclosure Agreement',
            default     => 'Document from {{company}}',
        };
    }

    private static function defaultBody(string $type): string
    {
        return match($type) {
            'job_offer' => "Dear {{name}},\n\nWe are pleased to extend this offer of employment for the position of {{position}}.\n\nPlease find the Job Offer document attached. Review it carefully and sign where indicated.\n\nWe look forward to welcoming you to the team.\n\nBest regards,\n{{company}}",
            'contract'  => "Dear {{name}},\n\nPlease find your Employment Contract attached for your review and signature.\n\nKindly sign and return the document at your earliest convenience.\n\nBest regards,\n{{company}}",
            'nda'       => "Dear {{name}},\n\nAs part of your onboarding, please find the Non-Disclosure Agreement attached.\n\nPlease review, sign, and return the document.\n\nBest regards,\n{{company}}",
            default     => "Dear {{name}},\n\nPlease find the attached document for your review.\n\nBest regards,\n{{company}}",
        };
    }
}
