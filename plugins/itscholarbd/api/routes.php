<?php
use ItScholarBd\Api\Models\User;
use ItScholarBd\Api\Models\UserDevice;
use ItScholarBd\Api\Models\NotificationHistory;
use ItScholarBd\Api\Models\PasswordOTP;
use ItScholarBd\Api\Models\Car;
use ItScholarBd\Api\Models\Spare;
use ItScholarBd\Api\Classes\Utility;

use Illuminate\Http\Request;

Route::get('/', function () {
    return Redirect::to('dashboard');
});




Route::group(['prefix' => 'api/v2'], function() {

    Route::post('/login', function (Request $req) {
        try {
            $data = $req->all();
            $credentials = [
                'login'    => $data['username'],
                'password' => $data['password']
            ];

            $remember = false; // TODO: this will come from post data
            $user = Auth::authenticate($credentials, $remember);
            if ($user->isBanned()) {
                Auth::logout();
                return response()->json(['status' => 0, 'status_text' => 'Sorry, this user is currently not activated. Please contact us for further assistance.'], 403);
            }
            return response()->json(['status' => 1, 'status_text' => 'successfully logged in', 'user_arr' => $user], 200);
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    
    Route::post('/sendOTPforPasswordRecovery',function(){
        try {
            $data = post();
            extract($data);
            $found = User::where('email',$email)->count();
            if(!$found){
                return response()->json(['status' => 0, 'data' => 'There is no account with this email.Please check spelling carefully.' ], 500);
            }
            $response = Utility::sendOTPforPasswordRecovery($email);
            if($response['status'] == 0){
                return response()->json(['status' => 0, 'data' => $response['msg']], 500);
            }
            return response()->json(['status' => 1, 'data' => $response['msg']], 200);
            
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });
    Route::post('/verifyPasswordOTP',function(){
        try {
            $data = post();
            extract($data);
            $valid = PasswordOTP::where(['email' => $email, 'otp'=>$otp])->count();
            if($valid){
                return response()->json(['status' => 1, 'data' => 'CODE is valid'], 200);
            }
            return response()->json(['status' => 0, 'data' => 'CODE is invalid'], 200);
            
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/resetPassword',function(){
        try {
            $data = post();
            extract($data);
            $user = User::where('email', $email)->first();
            $user->password = \Hash::make($password);
            $user->update();
            PasswordOTP::where(['email' => $email])->delete();
            return response()->json(['status' => 1, 'status_text' => 'Password reset successfully.'], 200);  
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });
    
    Route::post('recordSummery',function(){
        $days = post('days');
         $records['normal']['total'] = [
           'number' => Car::count(),
           'text'   => 'Total', 
           'filterParam' => ['type'=>'all'],
           'color' => ['bg'=>'#3D5B59','font'=>'#FFFFFF']  
         ];
         $records['normal']['in'] = [
           'number' => Car::whereIn('status_id', [
                                                   Car::ENTRY,
                                                   Car::REPAIREDANDIN,
                                                   Car::CANNOTBERESOLVEDANDIN
                                                   ])->count(),
           'text'   => 'Inside',
           'filterParam' => ['type'=>'in'],
           'color' => ['bg'=>'#B4F8C8', 'font'=>'#1D3B58']    
         ];
         $records['normal']['out'] = [
           'number' => Car::whereIn('status_id', [
                                                   Car::REPAIREDANDOUT,
                                                   Car::CANNOTBERESOLVEDANDOUT
                                                   ])->count(),
           'text'   => 'Outside',
           'filterParam' => ['type'=>'out'],
           'color' => ['bg'=>'#FCB5AC', 'font'=>'#1D3B58']   
              
         ];
         $records['normal']['repairedAndIn'] = [
           'number' => Car::whereIn('status_id', [
                                                   Car::REPAIREDANDIN
                                                   ])->count(),
           'text'   => 'Repaired And In',
           'filterParam' => ['type'=>'repairedAndIn'],  
           'color' => ['bg'=>'#B99095', 'font'=>'#FFFFFF']  
         ];
         $records['normal']['repairedAndOut'] = [
           'number' => Car::whereIn('status_id', [
                                                   Car::REPAIREDANDOUT
                                                   ])->count(),
           'text'   => 'Repaired And Out',
           'filterParam' => ['type'=>'repairedAndOut'],
           'color' => ['bg'=>'#65463E', 'font'=>'#FFFFFF']       
         ];
         $records['normal']['cannotBeResolved'] = [
           'number' => Car::whereIn('status_id', [
                                                   Car::CANNOTBERESOLVEDANDIN,
                                                   Car::CANNOTBERESOLVEDANDOUT
                                                   ])->count(),
           'text'   => 'Cannot be resolved',
           'filterParam' => ['type'=>'cannotBeResolved'],
           'color' => ['bg'=>'#970C10', 'font'=>'#FFFFFF']  
         ];
         $records['special']['last10days'] = [
           'number' => Car::whereDate('created_at', '>=',  \Carbon\Carbon::now()->subDays($days))->count(),
           'text'   => 'Last 10 Days',
           'filterParam' => ['type'=>'days','days'=>20],
           'color' => ['bg'=>'#A1AFA0', 'font'=>'#FFFFFF']  

         ];
         return response()->json(['status' => 1, 'data' => $records], 200);
    });
    Route::get('getModel',function(){
        try{
            return response()->json(['status' => 1, 'data' => Car::MODELLIST], 200);
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });
    Route::get('getUnit',function(){
        try{
            return response()->json(['status' => 1, 'data' => Car::UNITLIST], 200);
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('addORupdateRecord',function(){
        try {
            $data = post();
            extract($data);
            $car = isset($id) ? Car::find($id) : new Car;
            foreach($data as $key=>$value){
                $car->$key = $value;
            }
            isset($id) ? $car->update() : $car->save();
            return response()->json(['status' => 1, 'status_text' => 'Saved  successfully.'], 200);  
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('getRecord', function(){
        try {
            $data = post();
            $car = Car::with('spare')->find($data['id']);
            $car['status'] = ['id' => $car->status_id, 'text' => Car::STATUSLIST[$car->status_id]];
            $car['model'] = ['id' => $car->model_id, 'text' => Car::MODELLIST[$car->model_id]];
            $car['unit'] = ['id' => $car->unit_id, 'text' => Car::UNITLIST[$car->unit_id]];
            $ctionList = Utility::getStatusUpdateActionList($car->status_id);
            return response()->json(['status' => 1, 'data' => $car,'actions' => $ctionList ], 200);  
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('addORupdateSpareParts', function(){
        try {
                $data = post();
                extract($data);
                $spareObj = Spare::where('car_id',$car_id);
                $spare = $spareObj->count() ? $spareObj->first() : new Spare; 
                $spare->car_id = $car_id;
                $spare->content = $content;
                $spareObj->count() ? $spare->update() : $spare->save();
                return response()->json(['status' => 1, 'status_text' => 'Spare parts updated  successfully.'], 200);  
        }
        catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('getSpareParts', function(){
        try {
            $data = post();
            $spare = Spare::where('car_id',$data['car_id']);
            return response()->json(['status' => 1, 'data' => $spare ], 200);  
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('getStatusUpdatActionList', function(){
        try {
            $data = post();
            $ctionList = Utility::getStatusUpdateActionList($data['status']);
            return response()->json(['status' => 1, 'data' => $ctionList ], 200);  
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });
    Route::post('statusUpdate', function(){
        try {
            $data = post();
            extract($data);
            $car = Car::find($id);
            $car->status_id = $status;
            $car->update();
            return response()->json([ 'status' => 1, 
                                      'status_text' => 'Status updated succesfully',
                                      'statusObj'=>['id' => $status, 'text' => Car::STATUSLIST[$status]], 
                                      'actions'=> Utility::getStatusUpdateActionList($data['status'])
                                    ], 200);  
        } catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/carList', function(){

        try{
            $data = post();
            extract($data);
            $carList = Car::with('spare')->orderBy('id','desc');
            switch($type){
                case 'repaired':
                    $carList = $carList->where('status_id',Car::REPAIREDANDIN)
                                      ->orWhere('status_id',Car::REPAIREDANDOUT);
                    break;                      
                case 'in':
                    $carList = $carList->whereIn('status_id', [
                                        Car::ENTRY,
                                        Car::REPAIREDANDIN,
                                        Car::CANNOTBERESOLVEDANDIN
                                        ]); 
                    break;                                     
                case 'out':
                    $carList = $carList->whereIn('status_id', [
                                        Car::REPAIREDANDOUT,
                                        Car::CANNOTBERESOLVEDANDOUT
                                        ]); 
                    break;                 
                case 'cannotBeResolved':
                    $carList = $carList->whereIn('status_id', [
                                        Car::CANNOTBERESOLVEDANDIN,
                                        Car::CANNOTBERESOLVEDANDOUT
                                        ]); 
                    break;                                     
                case 'repairedAndIn':
                    $carList = $carList->where('status_id',Car::REPAIREDANDIN);
                    break;
                case 'repairedAndOut':
                    $carList = $carList->where('status_id',Car::REPAIREDANDOUT);
                    break;
                case 'days':
                    $carList = $carList->whereDate('created_at', '>=',  \Carbon\Carbon::now()->subDays($days));
                    break;    

            }
            $perpage   = 10;
            $count     = $carList->count();
            $skip      = intval(($page - 1) * $perpage);
            $carList = $carList->skip($skip)->take($perpage)->orderBy('id', 'desc')->get();
            //dd($carList->toArray()); exit;
            foreach($carList as $key => $car){
                    $status_id = $car['status_id'];
                    $carList[$key]['status'] = ['id' => $status_id, 'text' => Car::STATUSLIST[$status_id]];
                    $model_id = $car['model_id'];
                    $carList[$key]['model'] = ['id' => $model_id, 'text' => Car::MODELLIST[$model_id]];
                    $unit_id = $car['unit_id'];
                    $carList[$key]['unit'] = ['id' => $unit_id, 'text' => Car::UNITLIST[$unit_id]];
                    $carList[$key]['action'] = Utility::getStatusUpdateActionList($status_id);
                    
                    unset($carList[$key]['status_id']);
                    unset($carList[$key]['model_id']);
                    unset($carList[$key]['unit_id']);
                    unset($carList[$key]['created_at']);
                    unset($carList[$key]['updated_at']);

            }
            $total     = $count;
            $pages     = ceil($count / $perpage);
           
           
            return response()->json([
                                     'status' => 1, 
                                     'data' => $carList,
                                     'meta'=> [
                                                "page" => $page,
                                                "pages" => $pages,
                                                "perpage" => $perpage,
                                                "total" => $total
                                            ]
                                     ],200);

        }catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/addUserDevice', function(){
        
        try{
            $data = post();
            extract($data);
            $userDeviceObj = UserDevice::where(['user_id' => $user_id, 'device_token'=> $device_token]);
            $message = ($userDeviceObj->count() == 1)? 'User device updated successfully.' :
                                             'User device added successfully.';
            $userDevice = ($userDeviceObj->count() == 1) ? $userDeviceObj->first() : new UserDevice;
            $userDevice->device_uid = $data['device_uid'];
            $userDevice->user_id = $data['user_id'];
            $userDevice->device_token = $data['device_token'];
            $userDevice->device_name = $data['device_name'];
            $userDevice->device_model = $data['device_model'];
            $userDevice->manufacturer = $data['manufacturer'];
            $userDevice->device_version	 = $data['device_version'];
            $userDevice->device_type	 = $data['device_type'];
            $userDevice->status	 = 1;
            ($userDeviceObj->count() == 1) ? $userDevice->update() : $userDevice->save();
            
            return response()->json(['status' => 1, 'status_text' => $message ], 200);
        }
        catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/disableDevice',function(){
        try{
            $user_id = post('user_id');
            DB::table('user_devices')->where('user_id', $user_id)->update(array('status' => 0)); 
            return response()->json(['status' => 1, 'status_text' => 'Device disabled successfully.'], 200); 
        }
        catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/sendNotification', function(){
        try{
            $data = post();
            Utility::addNotification($data); 
            return response()->json(['status' => 1, 'status_text' => 'Notification sent successfully.'], 200); 
        }
        catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/readNotification', function(){
        try{
            $params = post();

            $notifications = NotificationHistory::where('user_id', $params['user_id']);
            $perpage   = 20;
            $count     = $notifications->count();
            $skip      = intval(($params['page'] - 1) * $perpage);
            $notifications = $notifications->skip($skip)->take($perpage)->orderBy('id', 'desc')->get();
            $total     = $count;
            $pages     = ceil($count / $perpage);
           
            $noticationsFormated = [];

            foreach($notifications as $index => $notification){
                $noticationsFormated[$index]['title'] = $notification->title;
                $noticationsFormated[$index]['description'] = $notification->description;
                $noticationsFormated[$index]['at'] = $notification->created_at->diffForHumans();
            }
            DB::table('notification_histories')->where('user_id', $params['user_id'])
                                               ->update(array('is_read' => 1));
            return response()->json([
                                     'status' => 1, 
                                     'data' => $noticationsFormated,
                                     'meta'=> [
                                                "page" => $params['page'],
                                                "pages" => $pages,
                                                "perpage" => $perpage,
                                                "total" => $total
                                            ]
                                     ],200);

        }catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/setNotificationAsRead', function(){
        try{
            $user_id = post('user_id');
            DB::table('notification_histories')->where('user_id', $user_id)->update(array('is_read' => 1)); 
            return response()->json(['status' => 1, 'status_text' => 'Set as read successfully.'], 200);   
        }
        catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

    Route::post('/newNotification', function(){
        try{
            $user_id = post('user_id');
            $notificationCount = NotificationHistory::where(['user_id' => $user_id,'is_read' => 0 ])->count(); 
            return response()->json(['status' => 1, 'notification_count' => $notificationCount], 200);   
        }
        catch (Exception $ex) {
            return response()->json(['status' => 0, 'status_text' => $ex->getMessage()], 500);
        }
    });

});



?>