<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PerfilPrestador;
use App\Models\PerfilSindico;
use App\Models\PerfilAdministrador;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar novo usuário com perfil específico
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validação básica comum a todos os tipos
            $baseRules = [
                'name' => 'required|string|max:255',
                'cpf_cnpj' => 'required|string|unique:users,cpf_cnpj',
                'whatsapp' => 'required|string|unique:users,whatsapp',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'user_type' => 'required|in:PRESTADOR,SINDICO,ADMIN_IMOVEIS'
            ];

            // Validações específicas por tipo de usuário
            $specificRules = [];
            $userType = $request->input('user_type');

            switch ($userType) {
                case 'PRESTADOR':
                    $specificRules = [
                        'nome_fantasia' => 'nullable|string|max:255',
                        'razao_social' => 'nullable|string|max:255',
                        'cep' => 'nullable|string|max:10',
                        'endereco' => 'nullable|string',
                        'cidade' => 'nullable|string|max:255',
                        'raio_atuacao' => 'nullable|array',
                        'segmentos_atuacao' => 'nullable|array',
                        'segmentos_atuacao.*' => 'string',
                        'regime_tributario' => 'nullable|string|max:50',
                        'data_nascimento' => 'nullable|date'
                    ];
                    break;

                case 'SINDICO':
                    $specificRules = [
                        'periodo_mandato_inicio' => 'nullable|date',
                        'periodo_mandato_fim' => 'nullable|date|after:periodo_mandato_inicio',
                        'subsyndic_info' => 'nullable|array'
                    ];
                    break;

                case 'ADMIN_IMOVEIS':
                    $specificRules = [
                        'razao_social_empresa' => 'nullable|string|max:255',
                        'nome_fantasia_empresa' => 'nullable|string|max:255',
                        'responsavel_empresa_nome' => 'nullable|string|max:255',
                        'cep_empresa' => 'nullable|string|max:10',
                        'endereco_empresa' => 'nullable|string',
                        'contrato_prestacao_servico_info' => 'nullable|array'
                    ];
                    break;
            }

            // Combinar todas as validações
            $rules = array_merge($baseRules, $specificRules);

            $messages = [
                'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado',
                'whatsapp.unique' => 'Este WhatsApp já está cadastrado',
                'email.unique' => 'Este email já está cadastrado',
                'password.confirmed' => 'A confirmação da senha não confere',
                'user_type.in' => 'Tipo de usuário inválido',
                'periodo_mandato_fim.after' => 'Data fim do mandato deve ser posterior à data de início',
                'segmentos_atuacao.*.string' => 'Cada segmento de atuação deve ser um texto válido'
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Usar transaction para garantir que tudo seja criado junto
            $result = DB::transaction(function () use ($request, $userType) {
                // 1. Criar o usuário base
                $user = User::create([
                    'name' => $request->name,
                    'cpf_cnpj' => $request->cpf_cnpj,
                    'whatsapp' => $request->whatsapp,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'user_type' => $userType,
                    'status' => 'PENDENTE'
                ]);

                // 2. Criar o perfil específico baseado no tipo
                $perfil = null;
                switch ($userType) {
                    case 'PRESTADOR':
                        $perfil = PerfilPrestador::create([
                            'user_id' => $user->id,
                            'nome_fantasia' => $request->nome_fantasia,
                            'razao_social' => $request->razao_social,
                            'cep' => $request->cep,
                            'endereco' => $request->endereco,
                            'cidade' => $request->cidade,
                            'raio_atuacao' => $request->raio_atuacao,
                            'segmentos_atuacao' => $request->segmentos_atuacao,
                            'regime_tributario' => $request->regime_tributario,
                            'data_nascimento' => $request->data_nascimento
                        ]);
                        break;

                    case 'SINDICO':
                        $perfil = PerfilSindico::create([
                            'user_id' => $user->id,
                            'periodo_mandato_inicio' => $request->periodo_mandato_inicio,
                            'periodo_mandato_fim' => $request->periodo_mandato_fim,
                            'subsyndic_info' => $request->subsyndic_info
                        ]);
                        break;

                    case 'ADMIN_IMOVEIS':
                        $perfil = PerfilAdministrador::create([
                            'user_id' => $user->id,
                            'razao_social_empresa' => $request->razao_social_empresa,
                            'nome_fantasia_empresa' => $request->nome_fantasia_empresa,
                            'responsavel_empresa_nome' => $request->responsavel_empresa_nome,
                            'cep_empresa' => $request->cep_empresa,
                            'endereco_empresa' => $request->endereco_empresa,
                            'contrato_prestacao_servico_info' => $request->contrato_prestacao_servico_info
                        ]);
                        break;
                }

                // 3. Criar token de acesso
                $token = $user->createToken('auth_token')->plainTextToken;

                return [
                    'user' => $user,
                    'perfil' => $perfil,
                    'token' => $token
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Usuário registrado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $result['user']->id,
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                        'cpf_cnpj' => $result['user']->cpf_cnpj,
                        'whatsapp' => $result['user']->whatsapp,
                        'user_type' => $result['user']->user_type,
                        'status' => $result['user']->status,
                        'created_at' => $result['user']->created_at,
                        'perfil' => $result['perfil']
                    ],
                    'token' => $result['token'],
                    'token_type' => 'Bearer'
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login do usuário
     */
    public function login(Request $request): JsonResponse {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'Email é obrigatório',
                'email.email' => 'Email deve ter um formato válido',
                'password.required' => 'Senha é obrigatória'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Tentar autenticar
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            $user = Auth::user();

            // Verificar se a conta está ativa
            if ($user->status === 'BLOQUEADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'Conta bloqueada. Entre em contato com o suporte.'
                ], 403);
            }

            // Revogar todos os tokens existentes
            $user->tokens()->delete();

            // Criar novo token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Carregar perfil baseado no tipo de usuário
            $perfil = null;
            switch ($user->user_type) {
                case 'PRESTADOR':
                    $perfil = $user->perfilPrestador;
                    break;
                case 'SINDICO':
                    $perfil = $user->perfilSindico;
                    break;
                case 'ADMIN_IMOVEIS':
                    $perfil = $user->perfilAdministrador;
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'cpf_cnpj' => $user->cpf_cnpj,
                        'whatsapp' => $user->whatsapp,
                        'user_type' => $user->user_type,
                        'status' => $user->status,
                        'email_verified_at' => $user->email_verified_at,
                        'perfil' => $perfil
                    ],
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request): JsonResponse {
        try {
            // Revogar o token atual
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout de todos os dispositivos
     */
    public function logoutAll(Request $request): JsonResponse {
        try {
            // Revogar todos os tokens do usuário
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado em todos os dispositivos'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados do usuário autenticado
     */
    public function me(Request $request): JsonResponse {
        try {
            $user = $request->user();

            // Carregar perfil baseado no tipo de usuário
            $perfil = null;
            switch ($user->user_type) {
                case 'PRESTADOR':
                    $perfil = $user->perfilPrestador;
                    break;
                case 'SINDICO':
                    $perfil = $user->perfilSindico;
                    break;
                case 'ADMIN_IMOVEIS':
                    $perfil = $user->perfilAdministrador;
                    break;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'cpf_cnpj' => $user->cpf_cnpj,
                        'whatsapp' => $user->whatsapp,
                        'user_type' => $user->user_type,
                        'status' => $user->status,
                        'email_verified_at' => $user->email_verified_at,
                        'created_at' => $user->created_at,
                        'perfil' => $perfil
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter dados do usuário',
            ], 500);
        }
    }

    /**
     * Atualizar senha
     */
    public function updatePassword(Request $request): JsonResponse {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ], [
                'current_password.required' => 'Senha atual é obrigatória',
                'new_password.required' => 'Nova senha é obrigatória',
                'new_password.min' => 'Nova senha deve ter pelo menos 8 caracteres',
                'new_password.confirmed' => 'Confirmação da nova senha não confere'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Verificar senha atual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Senha atual incorreta'
                ], 401);
            }

            // Atualizar senha
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Revogar todos os tokens exceto o atual
            $currentToken = $request->user()->currentAccessToken();
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Senha atualizada com sucesso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar senha',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar se email existe
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists
        ], 200);
    }

    /**
     * Verificar se CPF/CNPJ existe
     */
    public function checkCpfCnpj(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cpf_cnpj' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $exists = User::where('cpf_cnpj', $request->cpf_cnpj)->exists();

        return response()->json([
            'success' => true,
            'exists' => $exists
        ], 200);
    }
}
