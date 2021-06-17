<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Project;
// use App\Project;

class ProjectController extends Controller
{
    public function getAllProjects()
    {
        return Project::all();
    }

    public function insertProject(Request $request) {
        $project = new Project;
        $project->city_id = 1;
        $project->company_id = 1;
        $project->user_id = 1;
        $project->name = 'Nombre del proyecto';
        $project->execution_date = '2020-04-30';
        $project->is_active = 1;
        $project->save();

        return "Guardado";
    }

    public function updateProject() {
        $project = Project::find(2);
        $project->name = 'Proyecto de tecnología';
        $project->save();
    
        return "Actualizado";
    }

    public function renameIncativeProjects(){
        $project = Project::where('is_active', 0)
                            ->update(['name' => 'Projecto inactivo']);
            return "Projectos renombrados";
    }

    public function deleteProject() {
        $project = Project::where('project_id', '>', 15)->delete();
        return "Registros eliminados";
    }

}
