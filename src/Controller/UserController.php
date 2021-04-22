<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UsersRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{

    /** @var UsersRepository $userRepository */
    private $usersRepository;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var $user */
    private $user;

    /**
     * AuthController Constructor
     *
     * @param UsersRepository $usersRepository
     */
    public function __construct(UsersRepository $usersRepository,TokenStorageInterface $tokenStorage)
    {
        $this->usersRepository = $usersRepository;
        $this->tokenStorage = $tokenStorage;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * Delete user
     * @param Request $request
     *
     * @return Response
     */
    public function deleteUser(Request $request)
    {

        if($this->checkSelfUserOrAdmin($request)){
        $user = $this->usersRepository->deleteUser($request->get('email'));

        return new Response(sprintf('User %s successfully deleted', $user->getUsername()));}
        return new Response('You have no permissions to delete this user');
    }

    /**
     * Delete user
     * @param Request $request
     *
     * @return Response
     */
    public function checkSelfUserOrAdmin(Request $request){
        if($request->get('email')==$this->user->getEmail() || in_array('ROLE_ADMIN',$this->user->getRoles())){
            return true;
        }
        return false;
    }

    /**
     * Update user
     * @param Request $request
     *
     * @return Response
     */
    public function updateUser(Request $request)
    {

        if($this->checkSelfUserOrAdmin($request)){
            $user = $this->usersRepository->updateUser($request);

            return new Response(sprintf('User %s successfully updated', $user->getUsername()));}
        return new Response('You have no permissions to update this user');
    }

    /**
     * Get user
     * @param Request $request
     *
     * @return Response
     */
    public function getUser(Request $request)
    {

        if($this->checkSelfUserOrAdmin($request)){
            $user = $this->usersRepository->findOneByEmailField($request->get('email'));

            return new Response(sprintf('User %s successfully readed', $user->getUsername()));}
        return new Response('You have no permissions to read this user');
    }

}
