<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /** EntityManager $manager */
    private $manager;

    /** UserPasswordEncoderInterface $encoder */
    private $encoder;

    /**
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(ManagerRegistry $registry, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct($registry, Users::class);
        $this->manager= $registry->getManagerForClass(Users::class);
        $this->encoder = $encoder;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function findOneByEmailField($value): ?Users
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    /**
     * Create a new user
     * @param $data
     * @return Users
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createNewUser($data)
    {
        $user = new Users();
        if($data['is_admin']){
            $user->setEmail($data['email'])
                ->setPassword($this->encoder->encodePassword($user, $data['password']))->setRoles(['ROLE_ADMIN']);
        }
        else{
        $user->setEmail($data['email'])
            ->setPassword($this->encoder->encodePassword($user, $data['password']));}

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    /**
     * Delete user
     * @param $data
     * @return Users
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteUser($email)
    {
        $user=$this->findOneByEmailField($email);

        $this->_em->remove($user);
        $this->_em->flush();

        return new JsonResponse(array(
            'status' => 'success',
            'message' => ('USER_DELETED_SUCCESS')
        ));

    }

    /**
     * Update user
     * @param $data
     * @return Users
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateUser($data)
    {
        $user=$this->findOneByEmailField($data->get('email'));

        $user->setPassword($data->get('new_password'));
        $user->setEmail($data->get('new_email'));
        $this->_em->persist($user);
        $this->_em->flush();

        return new JsonResponse(array(
            'status' => 'success',
            'message' => ('USER_UPDATED_SUCCESS')
        ));

    }



}
