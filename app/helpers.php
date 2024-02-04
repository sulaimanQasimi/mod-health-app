<?php

use App\Models\DocumentTypeColumn;
use Illuminate\Support\Facades\Session;
use HanifHefaz\Dcter\Dcter;

function localize($message)
{
    if (!Session()->has('language')) {
        $localiz_message = \Lang::get($message, [], 'dr');
    } else {
        $localiz_message = \Lang::get($message, [], Session()->get('language'));
    }

    return $localiz_message;
}

function multiFileUpload($request,$max_id,$path)
{
    $rules = [
        'file.*' => 'sometimes|mimes:pdf,PDF',
    ];

    $messages = [
        'file.*' => 'please select a valid type',
    ];
    $request->validate($rules,$messages);


    if ($request->hasfile('file')) {
      $data=[];
      foreach ($request->file('file') as $index => $file) {

          $file_extension= $file->getClientOriginalExtension();
          $file_name = $path.'/'.$max_id.'_'.$index.'.'.$file->getClientOriginalExtension();

          if(in_array($file_extension,['pdf', 'PDF'])){

            $file->move($path,$file_name);
          }
          $data[$index]=['file'=>$file_name];
        }
      return $data;
    }else{
      return [];
    }
}

function storeFiles($request, $file_names, $document_id)
    {
        $path_array = [];
        foreach ($file_names as $type) {
            if ($request->hasFile($type)) {
                $path = 'storage/uploads/' . $type . '/';
                $extension = $request->file($type)->getClientOriginalExtension();
                if (in_array($extension, ['pdf','PDF'])) {
                    $fileName = $document_id . '-' . time() . '.' . $extension;
                    $request->file($type)->move(
                        base_path() . '/storage/app/public/uploads/' . $type,
                        $fileName
                    );
                    $path_array[$type] = $path . $fileName;
                }

            }
        }
        return $path_array;

    }

function storeImages($request, $file_names, $document_id)
    {
        $path_array = [];
        foreach ($file_names as $type) {
            if ($request->hasFile($type)) {
                $path = 'storage/uploads/' . $type . '/';
                $extension = $request->file($type)->getClientOriginalExtension();
                if (in_array($extension, ['jpg','JPG','png','PNG','JPEG','jpeg'])) {
                    $fileName = $document_id . '-' . time() . '.' . $extension;
                    $request->file($type)->move(
                        base_path() . '/storage/app/public/uploads/' . $type,
                        $fileName
                    );
                    $path_array[$type] = $path . $fileName;
                }

            }
        }
        return $path_array;

    }

    function currentUser()
        {
        $user = [];
        if (Session::has('current_user')) {
            $user = Session::get('current_user');
        } else {
            $user = auth()->user();
            Session::put('current_user', $user);
        }
        return $user;
        }

?>
