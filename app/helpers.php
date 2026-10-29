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

/**
 * Sanitize rich HTML (e.g. CKEditor lab text_result) before rendering or storing.
 * Allows basic formatting tags and strips scripts/event handlers.
 */
function sanitize_html(?string $html): string
{
    if ($html === null || trim($html) === '') {
        return '';
    }

    $allowed = '<p><br><br/><strong><b><em><i><u><ul><ol><li><table><thead><tbody><tr><th><td><span><div><h1><h2><h3><h4><h5><h6>';
    $clean = strip_tags($html, $allowed);

    // Remove inline event handlers (onclick, onerror, ...).
    $clean = preg_replace('/\s+on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? '';

    // Remove javascript: / data: URLs in href/src.
    $clean = preg_replace('/\s+(href|src|xlink:href)\s*=\s*(["\'])\s*(javascript|data|vbscript):.*?\2/i', '', $clean) ?? '';

    // Remove dangerous CSS expressions.
    $clean = preg_replace('/\s+style\s*=\s*(["\']).*?(expression|javascript).*?\1/i', '', $clean) ?? '';

    return $clean;
}

/**
 * Whitelist an ORDER BY direction from request input.
 */
function safe_sort_order(mixed $order, string $default = 'desc'): string
{
    $order = strtolower((string) $order);

    if (in_array($order, ['asc', 'desc'], true)) {
        return $order;
    }

    return strtolower($default) === 'asc' ? 'asc' : 'desc';
}

/**
 * Whitelist an ORDER BY column from request input.
 *
 * @param  array<int, string>  $allowed
 */
function safe_sort_column(mixed $column, array $allowed, string $default): string
{
    $column = (string) $column;

    return in_array($column, $allowed, true) ? $column : $default;
}

?>
