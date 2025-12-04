<?php

namespace App\Classes\Model;

use App\Classes\Enum\ExternalUserRole;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuItem
{
    private string $id;

    private string $name;

    private ?string $caption;

    private ?string $url;

    private ?string $iconClass;

    private string $linkClass = '';

    private string $userRole;

    private bool $active = false;

    private bool $hasChildren = false;
    private array $children = [];

    private array $linkAttributes = [];

    private AuthorizationCheckerInterface $authorizationChecker;
    private ExternalUserRole $externalUserRole;

    public function __construct(
        string $name,
        string $caption,
        string $url,
        string $iconClass,
        string $userRole = 'ROLE_NORMAL',
        ExternalUserRole $externalUserRole = ExternalUserRole::STAFF,
    ) {
        $this->name = $name;
        $this->caption = $caption;
        $this->url = $url;
        $this->iconClass = $iconClass;
        $this->userRole = $userRole;
        $this->externalUserRole = $externalUserRole;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return strtolower($this->name);
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function setCaption(?string $caption): void
    {
        $this->caption = $caption;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getIconClass(): ?string
    {
        return $this->iconClass;
    }

    public function setIconClass(?string $iconClass): void
    {
        $this->iconClass = $iconClass;
    }

    public function getLinkClass(): string
    {
        return $this->linkClass;
    }

    public function setLinkClass(string $linkClass): void
    {
        $this->linkClass = $linkClass;
    }

    public function getUserRole(): string
    {
        return $this->userRole;
    }

    public function setUserRole(string $userRole): void
    {
        $this->userRole = $userRole;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return MenuItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function hasChildren(): bool
    {
        return $this->hasChildren;
    }

    public function addChild(MenuItem $item): MenuItem
    {
        $this->hasChildren = true;
        $this->children[$item->getName()] = $item;

        return $item;
    }

    public function getAuthorizationChecker(): AuthorizationCheckerInterface
    {
        return $this->authorizationChecker;
    }

    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): void
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function checkAndSetActive(?array $slugs = null): void
    {
        if (empty($slugs)) {
            return;
        }
        if (empty($slugs['root'])) {
            return;
        }
        if ($slugs['root'] == $this->getName()) {
            $this->setActive(true);
        }
        if (empty($slugs['child'])) {
            return;
        }
        if ($this->hasChildren()) {
            foreach ($this->getChildren() as $childMenu) {
                $childMenu->checkAndSetActive(['root' => $slugs['child']]);
            }
        }
    }

    public function getGrantedMenu(): ?MenuItem
    {
        if (!$this->authorizationChecker->isGranted($this->getUserRole())) {
            return null;
        }

        if ($this->hasChildren()) {
            $filteredMenus = [];
            foreach ($this->getChildren() as $childMenu) {
                if ($this->authorizationChecker->isGranted($childMenu->getUserRole())) {
                    $filteredMenus[] = $childMenu;
                }
            }
            if (0 == count($filteredMenus)) {
                return null;
            }
            $this->setChildren($filteredMenus);
        }

        return $this;
    }

    public function getLinkAttributes(): array
    {
        return $this->linkAttributes;
    }

    public function setLinkAttributes(array $linkAttributes): void
    {
        $this->linkAttributes = $linkAttributes;
    }

    public function setHasChildren(bool $hasChildren): void
    {
        $this->hasChildren = $hasChildren;
    }

    public function getExternalUserRole(): ExternalUserRole
    {
        return $this->externalUserRole;
    }
}
