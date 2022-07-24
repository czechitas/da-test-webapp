<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Services\LogViewer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class LogViewerController extends Controller
{
    private LogViewer $logViewer;

    public function __construct(LogViewer $logViewer)
    {
        $this->logViewer = $logViewer;
    }

   /**
    * @return array<string, mixed>|mixed
    * @throws \Exception
    */
    public function index(Request $request)
    {
        $folderFiles = [];
        if ($request->input('f')) {
            $this->logViewer->setFolder(Crypt::decrypt($request->input('f')));
            $folderFiles = $this->logViewer->getFolderFiles(true);
        }
        if ($request->input('l')) {
            $this->logViewer->setFile(Crypt::decrypt($request->input('l')));
        }

        $early_return = $this->earlyReturn($request);
        if ($early_return) {
            return $early_return;
        }

        $data = [
            'logs' => $this->logViewer->all(),
            'folders' => $this->logViewer->getFolders(),
            'current_folder' => $this->logViewer->getFolderName(),
            'folder_files' => $folderFiles,
            'files' => $this->logViewer->getFiles(true),
            'current_file' => $this->logViewer->getFileName(),
            'standardFormat' => true,
        ];

        if ($request->wantsJson()) {
            return $data;
        }

        if (\is_array($data['logs']) && \count($data['logs']) > 0) {
            $firstLog = \reset($data['logs']);
            if (!$firstLog['context'] && !$firstLog['level']) {
                $data['standardFormat'] = false;
            }
        }

        return \view('admin.log_viewer', $data);
    }

    /**
     * @return bool|mixed
     * @throws \Exception
     */
    private function earlyReturn(Request $request)
    {
        if ($request->input('f')) {
            $this->logViewer->setFolder(Crypt::decrypt($request->input('f')));
        }

        if ($request->input('dl')) {
            return $this->download($this->pathFromInput($request, 'dl'));
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    private function pathFromInput(Request $request, string $input_string): string
    {
        return $this->logViewer->pathToLogFile(Crypt::decrypt($request->input($input_string)));
    }

    /**
     * @return mixed
     */
    private function download(string $data)
    {
        return \response()->download($data);
    }
}
