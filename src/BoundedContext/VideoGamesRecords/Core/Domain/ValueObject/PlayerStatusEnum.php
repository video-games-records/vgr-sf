<?php

declare(strict_types=1);

namespace App\BoundedContext\VideoGamesRecords\Core\Domain\ValueObject;

enum PlayerStatusEnum: string
{
    case MEMBER = 'MEMBER';
    case WEBMASTER = 'WEBMASTER';
    case DEVELOPER = 'DEVELOPER';
    case DESIGNER = 'DESIGNER';
    case GAME_ADDER = 'GAME_ADDER';
    case TRANSLATOR = 'TRANSLATOR';
    case MODERATOR = 'MODERATOR';
    case ADMINISTRATOR = 'ADMINISTRATOR';
    case REDACTOR = 'REDACTOR';
    case PROOF_ADMIN = 'PROOF_ADMIN';
    case REFEREE = 'REFEREE';
    case GAME_AND_PROOF_ADMIN = 'GAME_AND_PROOF_ADMIN';
    case CHIEF_PROOF_ADMIN = 'CHIEF_PROOF_ADMIN';
    case CHIEF_STAFF = 'CHIEF_STAFF';
    case STREAMER = 'STREAMER';

    public function getLabel(): string
    {
        return match ($this) {
            self::MEMBER => 'Member',
            self::WEBMASTER => 'Webmaster',
            self::DEVELOPER => 'Developer',
            self::DESIGNER => 'Designer',
            self::GAME_ADDER => 'Game Adder',
            self::TRANSLATOR => 'Translator',
            self::MODERATOR => 'Moderator',
            self::ADMINISTRATOR => 'Administrator',
            self::REDACTOR => 'Redactor',
            self::PROOF_ADMIN => 'Proof Admin',
            self::REFEREE => 'Referee',
            self::GAME_AND_PROOF_ADMIN => 'Game & Proof Admin',
            self::CHIEF_PROOF_ADMIN => 'Chief Proof Admin',
            self::CHIEF_STAFF => 'Chief Staff',
            self::STREAMER => 'Streamer',
        };
    }

    public function getFrenchLabel(): string
    {
        return match ($this) {
            self::MEMBER => 'Membre',
            self::WEBMASTER => 'Webmaster',
            self::DEVELOPER => 'Développeur',
            self::DESIGNER => 'Designer',
            self::GAME_ADDER => 'Ajouteur de Jeux',
            self::TRANSLATOR => 'Traducteur',
            self::MODERATOR => 'Modérateur',
            self::ADMINISTRATOR => 'Administrateur',
            self::REDACTOR => 'Rédacteur',
            self::PROOF_ADMIN => 'Admin Preuves',
            self::REFEREE => 'Arbitre',
            self::GAME_AND_PROOF_ADMIN => 'Admin Jeux & Preuves',
            self::CHIEF_PROOF_ADMIN => 'Chef Admin Preuves',
            self::CHIEF_STAFF => 'Chef du Personnel',
            self::STREAMER => 'Streameur',
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::MEMBER => 'member',
            self::WEBMASTER => 'webmaster',
            self::DEVELOPER => 'developer',
            self::DESIGNER => 'designer',
            self::GAME_ADDER => 'game_adder',
            self::TRANSLATOR => 'translator',
            self::MODERATOR => 'moderator',
            self::ADMINISTRATOR => 'administrator',
            self::REDACTOR => 'redactor',
            self::PROOF_ADMIN => 'proof_admin',
            self::REFEREE => 'referee',
            self::GAME_AND_PROOF_ADMIN => 'game_and_proof_admin',
            self::CHIEF_PROOF_ADMIN => 'chief_proof_admin',
            self::CHIEF_STAFF => 'chief_staff',
            self::STREAMER => 'streamer',
        };
    }

    /**
     * Check if this status has admin privileges
     */
    public function isAdmin(): bool
    {
        return in_array($this, [
            self::WEBMASTER,
            self::ADMINISTRATOR,
            self::PROOF_ADMIN,
            self::GAME_AND_PROOF_ADMIN,
            self::CHIEF_PROOF_ADMIN,
            self::CHIEF_STAFF,
        ]);
    }

    /**
     * Check if this status has moderation privileges
     */
    public function isModerator(): bool
    {
        return in_array($this, [
            self::MODERATOR,
            self::ADMINISTRATOR,
            self::CHIEF_STAFF,
        ]);
    }

    /**
     * Check if this status can manage proofs
     */
    public function canManageProofs(): bool
    {
        return in_array($this, [
            self::PROOF_ADMIN,
            self::REFEREE,
            self::GAME_AND_PROOF_ADMIN,
            self::CHIEF_PROOF_ADMIN,
            self::CHIEF_STAFF,
        ]);
    }

    /**
     * Check if this status can manage games
     */
    public function canManageGames(): bool
    {
        return in_array($this, [
            self::GAME_ADDER,
            self::GAME_AND_PROOF_ADMIN,
            self::ADMINISTRATOR,
            self::CHIEF_STAFF,
        ]);
    }

    /**
     * Get all available status cases
     * @return array<int, PlayerStatusEnum>
     */
    public static function getAllStatuses(): array
    {
        return self::cases();
    }

    /**
     * Get status by old ID (for compatibility with existing database)
     */
    public static function fromId(int $id): ?self
    {
        return match ($id) {
            1 => self::MEMBER,
            2 => self::WEBMASTER,
            3 => self::DEVELOPER,
            4 => self::DESIGNER,
            5 => self::GAME_ADDER,
            6 => self::TRANSLATOR,
            7 => self::MODERATOR,
            8 => self::ADMINISTRATOR,
            9 => self::REDACTOR,
            10 => self::PROOF_ADMIN,
            11 => self::REFEREE,
            12 => self::GAME_AND_PROOF_ADMIN,
            13 => self::CHIEF_PROOF_ADMIN,
            14 => self::CHIEF_STAFF,
            15 => self::STREAMER,
            default => null,
        };
    }
}
