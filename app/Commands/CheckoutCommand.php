<?php

namespace App\Commands;


use App\Actions\Git\GitCheckoutBranchAction as CheckoutBranch;
use App\Entities\LinearIssue;
use App\Services\Linear\Issue;
use App\Services\Linear\LinearApiGateway;
use App\Traits\HasLinearMenus;
use App\Traits\InteractsWithGitRepo;
use LaravelZero\Framework\Commands\Command;
use PhpSchool\CliMenu\Action\GoBackAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CheckoutCommand extends Command
{
    use InteractsWithGitRepo;
    use HasLinearMenus;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'checkout {issue?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Checkout a Linear Issue.';

    public function handle(CheckoutBranch $checkoutBranch): int
    {
        if(! $this->ensureTeamIsSet()) {
            return -1;
        }

        $this->ensureFolderHasGitRepo();

        if(! $this->ensureRepoIsClean()) {
            $this->alert('Repo has unstaged changes!!');
            return 0;
        }

        if(! $this->ensureCurrentBranchIsCorrect()) {
            $this->alert('Exiting..');
            return 0;
        }

        $issue = $this->getIssue();

        if(! $issue) {
            $this->error('No issue found!');
            return 0;
        }

        if(! $this->confirm(sprintf("Checkout %s", $issue->branchName))) {
            $this->info('exiting..');
            return 0;
        }

        try {
            $this->task(
                "Checkout branch $issue->branchName",
                fn() => $checkoutBranch->execute($issue->branchName)
            );

            $this->alert('Happy coding!');
        } catch (ProcessFailedException $e) {
            $this->error($e->getMessage());
        }

        return 0;
    }

    public function getIssue(): ?LinearIssue
    {
        if($identifier = $this->argument('issue')) {
            return app(LinearApiGateway::class)->issue()->find($identifier);
        }

        $activeCycle = app(LinearApiGateway::class)->cycle()->with(Issue::class)->active();

        $menu = $this->menu(sprintf(
            "%s - Select an issue for PR",
            $activeCycle->name ?? 'Cycle' . $activeCycle->number
        ));

        $triageIssues = $this->linear()->issues()->triage();

        $issues = $activeCycle->issues->merge($triageIssues);
        $states = $issues->groupBy('state');

        $states->each(
            fn($stateIssues, $state) => $menu->addSubMenu(
                $state,
                $this->subMenu($state, $stateIssues, $menu)
            )
        );

        return $menu->open();
    }

    public function subMenu($state, $stateIssues, $menu): callable
    {
        return function (CliMenuBuilder $b) use ($state, $stateIssues, $menu) {
            $b->disableDefaultItems()
                ->setTitle($state);

            collect($stateIssues)->each(function($issue) use($b, $menu) {
                $b->addItem($issue->title, function(CliMenu $cliMenu) use ($menu,$issue) {
                    $menu->setResult($issue);
                    $cliMenu->close();
                });
            });

            $b->addItem('..', new GoBackAction); //add a go back button
        };
    }
}
