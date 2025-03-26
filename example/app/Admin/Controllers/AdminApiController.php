<?php

namespace App\Admin\Controllers;

use App\Models\AdministratorApi;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class AdminApiController extends AdminController
{

    public function title()
    {
        return '员工管理';
    }

    protected function grid()
    {
        //dd(AdministratorApi::all()->toArray());
        return Grid::make(AdministratorApi::with(['roles']), function (Grid $grid) {

            $grid->column('admin_id', 'ID')->sortable();
            $grid->column('realname', '姓名');
            $grid->column('post', '职位');

            if (config('admin.permission.enable')) {
                $grid->column('roles')->pluck('name')->label('primary', 3);

                $permissionModel = config('admin.database.permissions_model');
                $roleModel = config('admin.database.roles_model');
                $nodes = (new $permissionModel())->allNodes();
                $grid->column('permissions')
                    ->if(function () {
                        return ! $this->roles->isEmpty();
                    })
                    ->showTreeInDialog(function (Grid\Displayers\DialogTree $tree) use (&$nodes, $roleModel) {
                        $tree->nodes($nodes);

                        foreach (array_column($this->roles->toArray(), 'slug') as $slug) {
                            if ($roleModel::isAdministrator($slug)) {
                                $tree->checkAll();
                            }
                        }
                    })
                    ->else()
                    ->display('');
            }

            $grid->column('ctime', '创建时间');
            $grid->column('last_login_time', '最后登录')->sortable();

            $grid->quickSearch(['admin_id', 'post', 'username']);

            $grid->disablePagination(); // 关闭分页- api分页没做
            $grid->showQuickEditButton();
            $grid->enableDialogCreate();
            $grid->showColumnSelector();

            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableCreateButton();
            $grid->disableViewButton();
            $grid->disableBatchDelete();

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($actions->getKey() == AdministratorApi::DEFAULT_ID) {
                    $actions->disableDelete();
                }
            });
        });
    }

    public function form()
    {
        return Form::make(AdministratorApi::with(['roles']), function (Form $form) {
            //$form->builder()->setResourceId(4);
//dd($form);
            $id = $form->getKey();

            $form->display('admin_id', 'ID');

            if (config('admin.permission.enable')) {
                $form->multipleSelect('roles', trans('admin.roles'))
                    ->options(function () {
                        $roleModel = config('admin.database.roles_model');

                        return $roleModel::all()->pluck('name', 'id');
                    })
                    ->customFormat(function ($v) {
                        return array_column($v, 'id');
                    });
            }

            if ($id == AdministratorApi::DEFAULT_ID) {
                $form->disableDeleteButton();
            }
        });
    }

    public function update($id)
    {
        return $this->form()->update($id);
    }

}
