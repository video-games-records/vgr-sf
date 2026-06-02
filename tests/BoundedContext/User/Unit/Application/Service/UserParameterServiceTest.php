<?php

declare(strict_types=1);

namespace App\Tests\BoundedContext\User\Unit\Application\Service;

use App\BoundedContext\User\Application\Service\UserParameterService;
use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Entity\UserParameter;
use App\BoundedContext\User\Domain\ValueObject\UserParameterKeyEnum;
use App\BoundedContext\User\Infrastructure\Persistence\Doctrine\UserParameterRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class UserParameterServiceTest extends TestCase
{
    private UserParameterRepository&MockObject $repository;
    private EntityManagerInterface&MockObject $em;
    private UserParameterService $service;
    private User $user;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserParameterRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->service = new UserParameterService($this->repository, $this->em);
        $this->user = new User();
    }

    // ------------------------------------------------------------------
    // getHomeDashboard
    // ------------------------------------------------------------------

    public function testGetHomeDashboardReturnsDefaultWhenNoParameter(): void
    {
        $this->repository->method('findOneByUserAndKey')->willReturn(null);

        $result = $this->service->getHomeDashboard($this->user);
        $this->assertSame(UserParameterKeyEnum::HOME_DASHBOARD->getDefault(), $result);
    }

    public function testGetHomeDashboardReturnsStoredValue(): void
    {
        $param = new UserParameter($this->user, UserParameterKeyEnum::HOME_DASHBOARD, 'ranking');
        $this->repository->method('findOneByUserAndKey')->willReturn($param);

        $result = $this->service->getHomeDashboard($this->user);
        $this->assertSame('ranking', $result);
    }

    // ------------------------------------------------------------------
    // getScoreFormPerPage
    // ------------------------------------------------------------------

    public function testGetScoreFormPerPageReturnsDefaultWhenNoParameter(): void
    {
        $this->repository->method('findOneByUserAndKey')->willReturn(null);

        $result = $this->service->getScoreFormPerPage($this->user);
        $this->assertSame((int) UserParameterKeyEnum::SCORE_FORM_PER_PAGE->getDefault(), $result);
    }

    public function testGetScoreFormPerPageReturnsStoredValue(): void
    {
        $param = new UserParameter($this->user, UserParameterKeyEnum::SCORE_FORM_PER_PAGE, '50');
        $this->repository->method('findOneByUserAndKey')->willReturn($param);

        $result = $this->service->getScoreFormPerPage($this->user);
        $this->assertSame(50, $result);
    }

    // ------------------------------------------------------------------
    // getFormData
    // ------------------------------------------------------------------

    public function testGetFormDataReturnsDefaultsWhenNoParameters(): void
    {
        $this->repository->method('findAllByUser')->willReturn([]);

        $result = $this->service->getFormData($this->user);

        $this->assertArrayHasKey(UserParameterKeyEnum::HOME_DASHBOARD->value, $result);
        $this->assertArrayHasKey(UserParameterKeyEnum::SCORE_FORM_PER_PAGE->value, $result);
        $this->assertSame(UserParameterKeyEnum::HOME_DASHBOARD->getDefault(), $result[UserParameterKeyEnum::HOME_DASHBOARD->value]);
        $this->assertSame(UserParameterKeyEnum::SCORE_FORM_PER_PAGE->getDefault(), $result[UserParameterKeyEnum::SCORE_FORM_PER_PAGE->value]);
    }

    public function testGetFormDataOverridesDefaultsWithStoredValues(): void
    {
        $param = new UserParameter($this->user, UserParameterKeyEnum::HOME_DASHBOARD, 'ranking');
        $this->repository->method('findAllByUser')->willReturn([
            UserParameterKeyEnum::HOME_DASHBOARD->value => $param,
        ]);

        $result = $this->service->getFormData($this->user);

        $this->assertSame('ranking', $result[UserParameterKeyEnum::HOME_DASHBOARD->value]);
        $this->assertSame(UserParameterKeyEnum::SCORE_FORM_PER_PAGE->getDefault(), $result[UserParameterKeyEnum::SCORE_FORM_PER_PAGE->value]);
    }

    // ------------------------------------------------------------------
    // saveParameters
    // ------------------------------------------------------------------

    public function testSaveParametersUpdatesExistingParameter(): void
    {
        $param = new UserParameter($this->user, UserParameterKeyEnum::HOME_DASHBOARD, 'community');
        $this->repository->method('findAllByUser')->willReturn([
            UserParameterKeyEnum::HOME_DASHBOARD->value => $param,
        ]);

        $this->em->expects($this->once())->method('flush');
        $this->em->expects($this->never())->method('persist');

        $this->service->saveParameters($this->user, [
            UserParameterKeyEnum::HOME_DASHBOARD->value => 'ranking',
        ]);

        $this->assertSame('ranking', $param->getValue());
    }

    public function testSaveParametersPersistsNewParameter(): void
    {
        $this->repository->method('findAllByUser')->willReturn([]);
        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $this->service->saveParameters($this->user, [
            UserParameterKeyEnum::HOME_DASHBOARD->value => 'ranking',
        ]);
    }

    public function testSaveParametersFlushesOnce(): void
    {
        $this->repository->method('findAllByUser')->willReturn([]);
        $this->em->expects($this->once())->method('flush');

        $this->service->saveParameters($this->user, [
            UserParameterKeyEnum::HOME_DASHBOARD->value => 'ranking',
            UserParameterKeyEnum::SCORE_FORM_PER_PAGE->value => '50',
        ]);
    }
}
