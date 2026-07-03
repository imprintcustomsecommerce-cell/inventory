<?php

namespace App\Support;

/**
 * A rotating, colorful palette for stat/KPI cards used across the dashboard
 * and index pages. Each entry provides the Tailwind classes for the card
 * background and its text/icon so colored cards stay readable.
 */
class CardPalette
{
    public const COLORS = [
        ['bg' => 'bg-violet-500',  'value' => 'text-white',    'label' => 'text-violet-100',  'sub' => 'text-violet-200',  'chip' => 'bg-white/20 text-white'],
        ['bg' => 'bg-blue-500',    'value' => 'text-white',    'label' => 'text-blue-100',    'sub' => 'text-blue-200',    'chip' => 'bg-white/20 text-white'],
        ['bg' => 'bg-emerald-500', 'value' => 'text-white',    'label' => 'text-emerald-100', 'sub' => 'text-emerald-200', 'chip' => 'bg-white/20 text-white'],
        ['bg' => 'bg-brand-400',   'value' => 'text-zinc-900', 'label' => 'text-zinc-800',    'sub' => 'text-zinc-700',    'chip' => 'bg-zinc-900/10 text-zinc-900'],
        ['bg' => 'bg-pink-500',    'value' => 'text-white',    'label' => 'text-pink-100',    'sub' => 'text-pink-200',    'chip' => 'bg-white/20 text-white'],
        ['bg' => 'bg-cyan-500',    'value' => 'text-white',    'label' => 'text-cyan-100',    'sub' => 'text-cyan-200',    'chip' => 'bg-white/20 text-white'],
    ];

    /** Palette entry for card index $i (wraps around). */
    public static function at(int $i): array
    {
        return self::COLORS[$i % count(self::COLORS)];
    }
}
