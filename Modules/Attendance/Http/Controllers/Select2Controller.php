<?php
namespace Modules\Attendance\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Select2Controller extends Controller
{
    /**
     * Return User List for the attendance users
     */
    public function getUsers(Request $request): JsonResponse
    {
        $response = [];
        $query    = User::query()
            ->select('id', 'name', 'email', 'department_id')
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', [
                    User::ROLE_ADMIN,
                    User::ROLE_SUPER_ADMIN,
                ]);
            })

            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'Like', "%$request->search%")
                    ->orWhere('email', 'Like', "%$request->search%")
                    ->orWhereHas('department', function ($query) use ($request) {
                        return $query->where('name', 'Like', "%$request->search%");
                    });
            });
        $list = $query->get();
        foreach ($list as $data) {
            $response[] = [
                "id"   => $data->id,
                "text" => "{$data->department->name} - $data->name ( $data->email)",
            ];
        }
        return response()->json([
            'success' => true,
            'data'    => $response,
        ]);
    }
}
