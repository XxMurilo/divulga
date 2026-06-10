# Seeds of Good

Sistema web de doação de alimentos desenvolvido como Trabalho de Conclusão de Curso (TCC) da ETEC.

---

# Sobre o Projeto

O Seeds of Good é uma plataforma desenvolvida com o objetivo de reduzir o desperdício alimentar por meio da conexão entre doadores e recebedores de alimentos.

O sistema permite que empresas e pessoas físicas disponibilizem alimentos excedentes para doação, enquanto usuários interessados podem visualizar, reservar e retirar esses produtos de forma organizada e segura.

Além disso, a plataforma conta com um módulo administrativo responsável pelo gerenciamento de usuários, denúncias e monitoramento geral das atividades.

---

# Justificativa

O desperdício de alimentos representa um dos principais desafios sociais, econômicos e ambientais da atualidade.

Diversos estabelecimentos descartam diariamente produtos ainda próprios para consumo devido à proximidade da data de vencimento ou excesso de estoque, enquanto grande parte da população enfrenta dificuldades de acesso à alimentação.

Diante desse cenário, o Seeds of Good busca contribuir para a redução desses impactos através da tecnologia, promovendo uma ponte entre quem possui alimentos disponíveis e quem necessita deles.

---

# Objetivos

## Objetivo Geral

Desenvolver uma aplicação web para gerenciamento e intermediação de doações de alimentos.

## Objetivos Específicos

- Facilitar o cadastro de doadores e recebedores;
- Permitir o gerenciamento de alimentos disponíveis para doação;
- Controlar reservas de alimentos;
- Disponibilizar ferramentas administrativas para supervisão do sistema;
- Promover o combate ao desperdício alimentar.

---

# Funcionalidades

## Doador

O perfil de doador possui acesso às seguintes funcionalidades:

- Cadastro de alimentos para doação;
- Alteração de informações dos alimentos cadastrados;
- Exclusão de doações;
- Visualização das reservas realizadas;
- Atualização do status das reservas;
- Gerenciamento da própria conta;
- Registro de denúncias.

## Recebedor

O perfil de recebedor possui acesso às seguintes funcionalidades:

- Visualização dos alimentos disponíveis;
- Pesquisa de alimentos;
- Criação de reservas;
- Cancelamento de reservas;
- Registro de denúncias.

## Administrador

O perfil administrativo possui acesso às seguintes funcionalidades:

- Gerenciamento de usuários;
- Consulta de denúncias;
- Consulta de alimentos cadastrados;
- Alteração da condição dos usuários;
- Monitoramento geral do sistema.

---

# Arquitetura do Sistema

O projeto foi desenvolvido seguindo uma arquitetura baseada na separação entre interface, regras de negócio e armazenamento de dados.

```text
Cliente (HTML, CSS e JavaScript)
            |
            v
Servidor PHP
            |
            v
Banco de Dados MySQL