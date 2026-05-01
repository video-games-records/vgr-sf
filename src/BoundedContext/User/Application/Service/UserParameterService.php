<?php

declare(strict_types=1);

namespace App\BoundedContext\User\Application\Service;

use App\BoundedContext\User\Domain\Entity\User;
use App\BoundedContext\User\Domain\Entity\UserParameter;
use App\BoundedContext\User\Domain\ValueObject\UserParameterKeyEnum;
use App\BoundedContext\User\Infrastructure\Persistence\Doctrine\UserParameterRepository;
use Doctrine\ORM\EntityManagerInterface;

class UserParameterService
{
    public function __construct(
        private readonly UserParameterRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function getScoreFormPerPage(User $user): int
    {
        $parameter = $this->repository->findOneByUserAndKey($user, UserParameterKeyEnum::SCORE_FORM_PER_PAGE);

        if ($parameter === null) {
            return (int) UserParameterKeyEnum::SCORE_FORM_PER_PAGE->getDefault();
        }

        return (int) $parameter->getValue();
    }

    /**
     * @return array<string, string> indexed by UserParameterKeyEnum->value, with defaults applied
     */
    public function getFormData(User $user): array
    {
        $existing = $this->repository->findAllByUser($user);

        $data = [];
        foreach (UserParameterKeyEnum::cases() as $key) {
            $data[$key->value] = isset($existing[$key->value])
                ? $existing[$key->value]->getValue()
                : $key->getDefault();
        }

        return $data;
    }

    /**
     * @param array<string, string> $values indexed by UserParameterKeyEnum->value
     */
    public function saveParameters(User $user, array $values): void
    {
        $existing = $this->repository->findAllByUser($user);

        foreach ($values as $keyValue => $value) {
            $key = UserParameterKeyEnum::from($keyValue);

            if (isset($existing[$keyValue])) {
                $existing[$keyValue]->setValue($value);
            } else {
                $this->em->persist(new UserParameter($user, $key, $value));
            }
        }

        $this->em->flush();
    }
}
