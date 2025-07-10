<?php

namespace App\Console\Commands;

use App\Enums\PlantTreeStatus;
use App\Helpers\ActivityLogHelper;
use App\Helpers\CronActivityLogHelper;
use App\Mail\CronErrorMail;
use App\Models\Client;
use App\Models\MoreTreeCertificate;
use App\Models\MoreTreeHistory;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PlantMoreTrees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'more_trees:plant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command plants trees for the selected clients/customers using MoreTrees API.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $this->line("[" . date('Y-m-d H:i:s') . "] Running Command \"" . $this->signature . "\"");

        try {
            $now = now();
            $hourAgo = $now->copy()->subHour(1);

            $moreTreeHistories = MoreTreeHistory::query()
                ->select('id', 'client_id', 'name', 'email')
                ->where('status', false)
                /*->whereBetween('created_at', [$hourAgo, $now])*/
                ->with(['client:id,email,name'])
                ->get();

            $this->line("[" . date('Y-m-d H:i:s') . "] " . $moreTreeHistories->count() . " Trees will be planted for " . $moreTreeHistories->pluck('client.id')->unique()->count() . " customers.");

            foreach ($moreTreeHistories as $key => $moreTreeHistory) {

                try {

                    $projects = $this->projectListData();
                    $randomProjectIndexId = array_rand($projects);
                    $tree_id = $projects[$randomProjectIndexId];

                    $project = $projects[$randomProjectIndexId] ?? 'any_tree';
                    $client_emails = explode(',',$moreTreeHistory->client->email);
                    $client_email = $client_emails[0];

                    $response = Http::withHeaders([
                        'Accept'          => 'application/json',
                        'Content-Type'    => 'application/json',
                        'Accept-Encoding' => 'gzip, deflate',
                        'X-API-KEY'       => config('services.more_trees.key'),
                    ])->post('https://transaction-management-service.platform.moretrees.eco/transaction-management-api/external/plant', [
                        'payment_account_code' => config('services.more_trees.account_code'),
                        'test' => config('custom.app_server'),
                        'plant_for_others' => true,
                        'project_id' => $randomProjectIndexId ?? '',
                        'recipients' => [
                            [
                                'email' => $client_email ?? '',
                                'name' => $moreTreeHistory->client->name ?? '',
                                'quantity' => 1
                            ],
                        ],
                    ]);

                    $this->line("[" . date('Y-m-d H:i:s') . "] API response:");
                    $this->line($response);

                    if (!$response->successful()) {
                        $this->error("[" . date('Y-m-d H:i:s') . "] Got unsuccessful response while planting a tree for client: " . $moreTreeHistory->client->name ?? '');
                        continue;
                    };

                    $responseJson = $response->json();

                    if (isset($responseJson['errors']) && !empty($responseJson['errors'])) {
                        $this->error("[" . date('Y-m-d H:i:s') . "] Got errors in response for client: " . $moreTreeHistory->client->name ?? '');
                        $this->error("[" . date('Y-m-d H:i:s') . "] Error: " . $responseJson['errors'][0]['msg'] ?? '');
                        continue;
                    };

                    try {

                        $moreTreeHistory->update([
                            'email'            => $moreTreeHistory->client->email ?? '',
                            'status'            => true,
                            'mail_sent_at'      => now(),
                            'credits_used'      => $responseJson['credits_used'] ?? null,
                            'project_id'        => $responseJson['project_id'] ?? null,
                            'tree_id'           => $tree_id ?? null,
                            'name'              => $responseJson['recipients'][0]['account_name'] ?? null,
                            'account_code'      => $responseJson['recipients'][0]['account_code'] ?? null,
                        ]);
                        $workspace_id = $moreTreeHistory->client->workspace->id;
                        CronActivityLogHelper::log(
                            'tree_planted',
                            'Successful planting of '. $moreTreeHistory->client->name ?? '' .' tree',
                            [],
                            request(),
                            User::role('Superadmin')->first(),
                            $moreTreeHistory,
                            $workspace_id
                        );
                    } catch (\Throwable $th) {

                        $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while saving moretrees certificate in database for client: " . $moreTreeHistory->client->name ?? '');
                        $this->error("[" . date('Y-m-d H:i:s') . "] Error: " . $th->getMessage());
                        $this->error($th);
                        $this->sendCronErrorMail($th, $th->getMessage());
                    }
                } catch (\Throwable $th) {

                    $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while calling api to plant tree for client: " . $moreTreeHistory->client->name ?? '');
                    $this->error("[" . date('Y-m-d H:i:s') . "] Error: " . $th->getMessage());
                    $this->error($th);
                    $this->sendCronErrorMail($th, $th->getMessage());
                }
            }

            $this->info("[" . date('Y-m-d H:i:s') . "] Tree planted successfully.");
        } catch (\Throwable $th) {

            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while plating trees: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        }

        return 0;
    }

    public function projectListData()
    {
        $response = Http::get('https://project-management-service.platform.moretrees.eco/project-management-api/external/projects');

        try {
            if ($response->successful()) {
                $data = $response->json();
                $projects = $data['data'];

                // Process each project
                $country_name = [];
                foreach ($projects as $project) {
                    if($project['trees'][0]['credits_required'] <= 1.0){
                        $tree_id = $project['trees'][0]['id'];
                        $country_name[$project['id']] = $tree_id;
                    }
                }

                return $country_name; // Return the processed project data
            } else {
                // Handle unsuccessful response
                return response()->json(['error' => 'Failed to fetch data from API'], 500);
            }
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while fetch data from API: " . $th->getMessage());
            $this->error($th);
            $this->sendCronErrorMail($th, $th->getMessage());
        }
    }

    public function sendCronErrorMail(\Throwable $th = null, string $title = '')
    {
        try {
            Mail::to(
                config(
                    'custom.cron_email_recipients.error_mail_address',
                    []
                )
            )
                ->send(new CronErrorMail($title, $th, "Tree plant CRON - Error occurred"));
        } catch (\Throwable $th) {
            $this->error("[" . date('Y-m-d H:i:s') . "] Error occurred while sending mail: " . $th->getMessage());
            $this->error($th);
        }
    }
}
