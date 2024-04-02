<?php

namespace App\Console\Commands;

use Carbon;
use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\Contact;
use App\Models\Param;
use Illuminate\Support\Facades\Mail;


class TaskEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mail of tasks';

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
        // $tasks = Task::all();
        $etats = Param::where([['param_code', 'Etat'], ['code', ['En cours', 'Validé']], ['is_active', 1]])->pluck('id');
        $tasks = Task::select('id','callback_date','apporteur_id','is_sent')->whereIn('etat_id', $etats)->get();

        $date1 = Carbon\Carbon::now();

        if (!empty($tasks)) {
            foreach ($tasks as $task) {

                if($task->apporteur_id!=null){

                    $date2 = Carbon\Carbon::createFromTimestamp(strtotime($task->callback_date));
                  
                    if($task->is_sent==0){
                        if(($date1->toDateString() == $date2->toDateString()) || ($date1->toDateString() > $date2->toDateString())){
                            if ($task->apporteur_id> 0) {
                                $contact = Contact::findOrFail($task->apporteur_id);
                            }
    
                            if($contact->email){
                                $fullname= $contact->firstname." ".$contact->lastname;

                                $dt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $task->callback_date);
                                $ended_at = $dt->format('d/m/Y H:i:s');
                
                                $content = "Bonjour ".$fullname.","."<br/><br/>"."Vous avez lancé un rappel pour "."<a href='http://preprod.solaris-crfpe.fr/view/task/".$task->id."'>votre tâche</a>"."<br/><br/>"."Votre tâche arrivera à échéance le ".$ended_at;
                                $header="Environnement de formation pour CRFPE";
                                $footer = "Plateforme de formation SOLARIS";
                                $dn = Carbon\Carbon::now();

                                Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m) use ($contact,$fullname) {
                                    $m->from(auth()->user()->email);
                                    $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                                    $m->to($contact->email, $fullname)->subject('Rappel de tâche!');
                                });

                                $taskupdate = Task::find($task->id);
                                $taskupdate->is_sent = 1;
                                $taskupdate->save();

                                $this->info('Votre Mail a été bien envoyé Id : '.$task->id);
                            }else{
                                $this->info('Pas d\'adresse mail Id : '.$task->id);
                            }
                        }else{ 
                            $this->info('Votre Mail n\'a pas été envoyé Id : '.$task->id);
                        }
                    }else{
                        $this->info('déjà envoyé un mail pour Id : '.$task->id);
                    }
                }
            }
        }
    }
}
