<?php

declare(strict_types=1);

namespace App\Tests\SharedKernel\Unit\Domain\Security;

use App\SharedKernel\Domain\Security\SecurityEventTypeEnum;
use PHPUnit\Framework\TestCase;

class SecurityEventTypeEnumTest extends TestCase
{
    public function testGetCodeReturnsCaseValue(): void
    {
        $this->assertSame('password_change', SecurityEventTypeEnum::PASSWORD_CHANGE->getCode());
        $this->assertSame('login_success', SecurityEventTypeEnum::LOGIN_SUCCESS->getCode());
        $this->assertSame('registration', SecurityEventTypeEnum::REGISTRATION->getCode());
    }

    public function testGetLabelForPasswordEvents(): void
    {
        $this->assertSame('Password Changed', SecurityEventTypeEnum::PASSWORD_CHANGE->getLabel());
        $this->assertSame('Password Reset Requested', SecurityEventTypeEnum::PASSWORD_RESET_REQUEST->getLabel());
        $this->assertSame('Password Reset Completed', SecurityEventTypeEnum::PASSWORD_RESET_COMPLETE->getLabel());
    }

    public function testGetLabelForAuthEvents(): void
    {
        $this->assertSame('Login Success', SecurityEventTypeEnum::LOGIN_SUCCESS->getLabel());
        $this->assertSame('Login Failure', SecurityEventTypeEnum::LOGIN_FAILURE->getLabel());
        $this->assertSame('Logout', SecurityEventTypeEnum::LOGOUT->getLabel());
        $this->assertSame('Account Locked', SecurityEventTypeEnum::ACCOUNT_LOCKED->getLabel());
    }

    public function testGetIconReturnsKeyIconForPasswordAndApiEvents(): void
    {
        $this->assertSame('fa-key', SecurityEventTypeEnum::PASSWORD_CHANGE->getIcon());
        $this->assertSame('fa-key', SecurityEventTypeEnum::API_KEY_CREATED->getIcon());
        $this->assertSame('fa-key', SecurityEventTypeEnum::API_KEY_REVOKED->getIcon());
    }

    public function testGetIconReturnsEnvelopeForEmailEvents(): void
    {
        $this->assertSame('fa-envelope', SecurityEventTypeEnum::EMAIL_CHANGE->getIcon());
        $this->assertSame('fa-envelope', SecurityEventTypeEnum::EMAIL_VERIFY->getIcon());
    }

    public function testGetIconForSpecificEvents(): void
    {
        $this->assertSame('fa-sign-out-alt', SecurityEventTypeEnum::LOGOUT->getIcon());
        $this->assertSame('fa-lock', SecurityEventTypeEnum::ACCOUNT_LOCKED->getIcon());
        $this->assertSame('fa-user-plus', SecurityEventTypeEnum::REGISTRATION->getIcon());
        $this->assertSame('fa-shield-alt', SecurityEventTypeEnum::TWO_FACTOR_ENABLED->getIcon());
        $this->assertSame('fa-exclamation-triangle', SecurityEventTypeEnum::BRUTE_FORCE_ATTEMPT->getIcon());
    }

    public function testGetSeverityCriticalForDangerousEvents(): void
    {
        $this->assertSame('critical', SecurityEventTypeEnum::ACCOUNT_LOCKED->getSeverity());
        $this->assertSame('critical', SecurityEventTypeEnum::BRUTE_FORCE_ATTEMPT->getSeverity());
        $this->assertSame('critical', SecurityEventTypeEnum::SUSPICIOUS_ACTIVITY->getSeverity());
    }

    public function testGetSeverityErrorForLoginFailure(): void
    {
        $this->assertSame('error', SecurityEventTypeEnum::LOGIN_FAILURE->getSeverity());
    }

    public function testGetSeverityWarningEvents(): void
    {
        $this->assertSame('warning', SecurityEventTypeEnum::PASSWORD_RESET_REQUEST->getSeverity());
        $this->assertSame('warning', SecurityEventTypeEnum::USER_IMPERSONATE->getSeverity());
        $this->assertSame('warning', SecurityEventTypeEnum::TWO_FACTOR_DISABLED->getSeverity());
    }

    public function testGetSeveritySuccessEvents(): void
    {
        $this->assertSame('success', SecurityEventTypeEnum::LOGIN_SUCCESS->getSeverity());
        $this->assertSame('success', SecurityEventTypeEnum::REGISTRATION->getSeverity());
        $this->assertSame('success', SecurityEventTypeEnum::EMAIL_VERIFY->getSeverity());
        $this->assertSame('success', SecurityEventTypeEnum::TWO_FACTOR_ENABLED->getSeverity());
    }

    public function testGetSeverityInfoForDefaultEvents(): void
    {
        $this->assertSame('info', SecurityEventTypeEnum::EMAIL_CHANGE->getSeverity());
        $this->assertSame('info', SecurityEventTypeEnum::PROFILE_UPDATE->getSeverity());
        $this->assertSame('info', SecurityEventTypeEnum::ADMIN_LOGIN->getSeverity());
    }

    public function testGetCssClassMapsFromSeverity(): void
    {
        $this->assertSame('bg-danger', SecurityEventTypeEnum::ACCOUNT_LOCKED->getCssClass());
        $this->assertSame('bg-danger', SecurityEventTypeEnum::LOGIN_FAILURE->getCssClass());
        $this->assertSame('bg-warning', SecurityEventTypeEnum::PASSWORD_RESET_REQUEST->getCssClass());
        $this->assertSame('bg-success', SecurityEventTypeEnum::LOGIN_SUCCESS->getCssClass());
        $this->assertSame('bg-info', SecurityEventTypeEnum::EMAIL_CHANGE->getCssClass());
    }

    public function testIsReturnsTrueWhenCodeMatches(): void
    {
        $this->assertTrue(SecurityEventTypeEnum::PASSWORD_CHANGE->is('password_change'));
        $this->assertTrue(SecurityEventTypeEnum::LOGIN_SUCCESS->is('login_success'));
    }

    public function testIsReturnsFalseWhenCodeDoesNotMatch(): void
    {
        $this->assertFalse(SecurityEventTypeEnum::PASSWORD_CHANGE->is('login_success'));
        $this->assertFalse(SecurityEventTypeEnum::LOGOUT->is('login_success'));
    }

    public function testGetOptionsForFormContainsAllCases(): void
    {
        $options = SecurityEventTypeEnum::getOptionsForForm();

        $this->assertCount(count(SecurityEventTypeEnum::cases()), $options);
        $this->assertContains('password_change', $options);
        $this->assertContains('login_success', $options);
        $this->assertArrayHasKey('Password Changed', $options);
        $this->assertArrayHasKey('Login Success', $options);
    }

    public function testGetOptionsForFormUsesLabelAsKey(): void
    {
        $options = SecurityEventTypeEnum::getOptionsForForm();

        $this->assertSame('password_change', $options['Password Changed']);
        $this->assertSame('registration', $options['Registration']);
    }
}
