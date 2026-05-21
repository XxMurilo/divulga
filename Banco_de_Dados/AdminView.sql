
CREATE VIEW VerUsuarios AS
    SELECT 
    Usuario.NOME,
    Usuario.EMAIL,
    Usuario.TELEFONE,
    Usuario.ENDERECO,
    Permissao.NOME AS PERMISSÃO,
    Condicao.NOME AS CONDIÇÃO 
    FROM Usuario
    INNER JOIN Permissao ON Permissao.IDPERMISSAO = Usuario.IDPERMISSAO
    INNER JOIN Condicao ON Condicao.IDCONDICAO = Usuario.IDCONDICAO
    ORDER BY Usuario.NOME;


CREATE VIEW VerAlimentos AS
    SELECT
    Usuario.NOME AS DOADOR,
    Alimento_doador.QUANTIDADE,
    Alimento_doador.VALIDADE,
    Alimento_doador.DESCRICAO,
    Alimento.NOME AS Alimento
    FROM Alimento_doador
    INNER JOIN Usuario ON Usuario.IDUSUARIO = Alimento_doador.IDUSUARIO
    INNER JOIN Alimento ON Alimento.IDALIMENTO = Alimento_doador.IDALIMENTO
    ORDER BY Alimento_doador.VALIDADE;


CREATE VIEW VerDenuncias AS
    SELECT
    