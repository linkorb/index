<?php

namespace Index\Provider\GitHub;

use Index\Model\ProviderInterface;

class GitHubProvider implements ProviderInterface
{
    public function getTypes()
    {
        return [
            \Index\Provider\GitHub\Type\GitHubRepositoryType::class,
            \Index\Provider\GitHub\Type\GitHubPullRequestType::class
        ];
    }
}
