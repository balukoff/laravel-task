<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Balance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Balance {from_user_id} {to_user_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "The command takes the parameters {user user amount} to perform transfer operation.\nUsage is: ./artisan command:Balance {from_user_id to_user_id amount}";
    protected $no_amount   = "User does not have available amount ";
    protected $no_user     = "One of user_id's is not valid";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    
    private function validate(){
     $params = ['from_user_id', 'to_user_id', 'amount'];
     foreach($params as $item){
      $getval = intval($this->arguments()[$item]);
      if(!$getval){
       return false; 
      }
      
    }
      return true;
    }

    private function getError($msg){
     return $msg;    
    }
    
    private function UserExists($user_id){
     $user_count = DB::table('users')->where('user_id', $user_id)->first();
     return count($user_count) !== 0? true: false;
    }
    
    private function checkHaveAmount($user_id){
     $select = DB::table('users')->where('user_id', $user_id)->first();
     return $select->balance - $this->arguments()['amount'] > 0 ? true : false; 
    }
    
/**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
     $message = $this->description;
     
     if($this->validate()){
       if(($this->UserExists($this->arguments()['from_user_id']))&&($this->UserExists($this->arguments()['to_user_id']))){
              
       if($this->checkHaveAmount($this->arguments()['from_user_id'])){
       
       DB::transaction(function(){
        $amount = $this->arguments()['amount'];
        DB::update("update users set balance = balance - ? where user_id = ?", [$amount, $this->arguments()['from_user_id']]);
        DB::update("update users set balance = balance + ? where user_id = ?", [$amount, $this->arguments()['to_user_id']]);
        });
        
       }else
         $message = $this->no_amount;
        
       
       }else
         $message = $this->no_user;
       }
     
     $this->info($message);
    }
}
