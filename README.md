# Sistema de Microsserviços - Auth e Messageria

Este projeto consiste em dois microsserviços:
1. Auth API - Serviço de autenticação
2. Messageria API - Serviço de mensagens

## Estrutura do Projeto

```
.
├── auth-api/           # Serviço de autenticação
├── messageria_express/ # Serviço de mensagens
├── docker-compose.yaml # Orquestração dos containers
└── postman_collection.json # Coleção de testes
```

## Requisitos

- Docker
- Docker Compose
- Git

## Como Executar

1. Clone o repositório:
```bash
git clone [URL_DO_REPOSITORIO]
cd [NOME_DO_DIRETORIO]
```

2. Inicie os serviços:
```bash
docker-compose up -d
```

3. Os serviços estarão disponíveis em:
   - Auth API: http://localhost:8080
   - Messageria API: http://localhost:3001
   - RabbitMQ Management: http://localhost:15672 (guest/guest)

## Testando a Aplicação

1. Importe a coleção `postman_collection.json` no Postman
2. Execute os testes na seguinte ordem:
   - Auth API > Criar Usuário
   - Auth API > Login
   - Messageria API > Enviar Mensagem
   - Messageria API > Receber Mensagens

## Serviços

### Auth API
- Porta: 8080
- Banco de Dados: PostgreSQL
- Cache: Redis
- Endpoints:
  - POST /users/create - Criar usuário
  - POST /users/login - Login
  - GET /users/fetch - Buscar usuário
  - GET /health - Health check

### Messageria API
- Porta: 3001
- Message Broker: RabbitMQ
- Cache: Redis
- Endpoints:
  - POST /api/message - Enviar mensagem
  - POST /api/message/worker - Receber mensagens
  - GET /api/health - Health check

## Variáveis de Ambiente

### Auth API
- POSTGRES_DB: authdb
- POSTGRES_USER: authuser
- POSTGRES_PASSWORD: authpass

### Messageria API
- REDIS_HOST: messageria-redis
- REDIS_PORT: 6379
- RABBITMQ_HOST: rabbitmq
- RABBITMQ_PORT: 5672

## Contribuição

1. Faça um fork do projeto
2. Crie uma branch para sua feature (`git checkout -b feature/nova-feature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/nova-feature`)
5. Abra um Pull Request 