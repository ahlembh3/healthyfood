<?php
declare(strict_types=1);

namespace App\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

final class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private LoggerInterface $logger,
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $username = $user instanceof UserInterface ? $user->getUserIdentifier() : (string) $user;
        $roles = $user instanceof UserInterface ? $user->getRoles() : [];

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            $this->logger->info('Connexion admin réussie', ['user' => $username]);
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        $this->logger->info('Connexion utilisateur réussie', ['user' => $username]);
        return new RedirectResponse($this->router->generate('utilisateurs_dashboard'));
    }
}
