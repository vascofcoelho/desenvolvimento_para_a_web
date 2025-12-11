-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: rabbithead
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `artigos`
--

DROP TABLE IF EXISTS `artigos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `artigos` (
  `id_artigo` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `texto_artigo` text NOT NULL,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `foto` varchar(255) DEFAULT NULL,
  `id_categoria` int DEFAULT NULL,
  `URL_slug` varchar(255) NOT NULL DEFAULT '',
  `autor` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_artigo`),
  KEY `fk_artigos_categoria` (`id_categoria`),
  CONSTRAINT `fk_artigos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artigos`
--

LOCK TABLES `artigos` WRITE;
/*!40000 ALTER TABLE `artigos` DISABLE KEYS */;
INSERT INTO `artigos` VALUES (1,'IA na Indústria','A Inteligência Artificial (AI) está a transformar profundamente a indústria, impulsionando uma nova era de eficiência, inovação e competitividade. À medida que as empresas adotam soluções inteligentes, processos que antes dependiam exclusivamente de intervenção humana passam a ser automatizados, otimizados e orientados por dados em tempo real.\r\n\r\nNa produção, a AI permite a implementação de manutenção preditiva, detetando falhas em equipamentos antes que ocorram paragens dispendiosas. Sistemas de visão computacional melhoram o controlo de qualidade, identificando defeitos com maior precisão e velocidade. Já os algoritmos de aprendizagem automática ajustam cadeias de abastecimento, prevendo a procura, reduzindo desperdícios e garantindo entregas mais eficientes.\r\n\r\nAlém da operação, a AI também influencia o desenvolvimento de novos produtos, simulando protótipos, testando materiais e acelerando a inovação. A análise avançada de dados fornece insights valiosos para decisões estratégicas, desde a gestão de stocks até à personalização de soluções para clientes específicos.\r\n\r\nApesar dos benefícios, a integração da AI na indústria exige planeamento e uma abordagem responsável. A formação de equipas, a segurança de dados e a ética no uso da tecnologia são fatores essenciais para garantir uma implementação sustentável.\r\n\r\nA indústria do futuro será cada vez mais inteligente, conectada e automatizada — e a Inteligência Artificial ocupa um papel central nesta transformação, abrindo caminho para processos mais ágeis, seguros e competitivos.','2025-12-04 17:17:36','imgs/1765122100_ai.jpg',1,'ia-na-industria',2),(2,'Final da Liga dos Campeões','No dia 31 de maio de 2025, a Allianz Arena, em Munique, foi palco de uma final histórica da Liga dos Campeões, que ficará para sempre marcada na memória dos adeptos do futebol. O duelo colocou frente a frente o Paris Saint-Germain e o Inter de Milão, com a equipa francesa a protagonizar uma das atuações mais dominantes de sempre numa decisão europeia. Desde os primeiros minutos, o PSG mostrou-se superior, assumindo o controlo do jogo e criando oportunidades consecutivas que rapidamente se traduziram em golos. O resultado final, um expressivo 5–0, representou a maior goleada já registada numa final da competição e confirmou a ambição e evolução do clube parisiense ao mais alto nível.\r\n\r\nEntre os destaques da partida esteve o jovem Désiré Doué, que brilhou com uma exibição decisiva e foi eleito o melhor jogador em campo, simbolizando uma nova geração de talento que ajudou o PSG a alcançar o tão aguardado primeiro título da Liga dos Campeões. A equipa demonstrou maturidade tática, intensidade ofensiva e segurança defensiva, não dando qualquer hipótese ao adversário italiano, que lutou mas nunca conseguiu equilibrar o encontro.\r\n\r\nPara os adeptos, a vitória significou muito mais do que um troféu: foi a concretização de anos de investimento, trabalho e expectativas. Apesar de algumas celebrações terem sido marcadas por episódios de excessos, o sentimento geral foi de euforia e orgulho. Esta final deixou claro que o futebol europeu está cada vez mais aberto a novos protagonistas e que a dedicação, aliada ao talento e à organização, pode mesmo transformar sonhos em realidade.','2025-12-04 17:17:36','imgs/1765033258_final_champions.jpg',2,'final-da-liga-dos-campeoes',3),(3,'Dicas para estudar melhor','Estudar bem não depende apenas de passar horas em frente aos livros, mas sim de usar o tempo de forma inteligente e com qualidade. Uma das primeiras dicas para melhorar seus estudos é criar uma rotina organizada. Definir horários específicos para estudar ajuda o cérebro a se adaptar ao hábito e aumenta a concentração. Além disso, escolher um local tranquilo, bem iluminado e livre de distrações faz toda a diferença para manter o foco.\r\n\r\nOutra estratégia importante é estabelecer metas claras para cada sessão de estudo. Saber exatamente o que você precisa aprender evita a sensação de estar estudando sem direção e aumenta a produtividade. Também é fundamental fazer pausas regulares. Pequenos intervalos permitem que o cérebro descanse e ajudam a manter a atenção por mais tempo, evitando o cansaço mental.\r\n\r\nVariar as técnicas de estudo pode tornar o aprendizado mais dinâmico e eficiente. Ler, fazer resumos, criar mapas mentais ou explicar a matéria em voz alta são formas de reforçar a compreensão do conteúdo. Revisar o que foi estudado também é essencial para fixar o aprendizado, pois a repetição em intervalos ajuda a consolidar a memória.\r\n\r\nPor fim, cuidar da saúde é uma parte indispensável do processo. Dormir bem, alimentar-se de forma equilibrada e praticar alguma atividade física contribuem para melhorar a concentração e a disposição. Com organização, constância e métodos adequados, estudar se torna mais produtivo e até mais prazeroso, trazendo resultados melhores ao longo do tempo.','2025-12-04 17:17:36','imgs/1765302304_transferir__5_.jpeg',3,'dicas-para-estudar-melhor',4),(4,'Benefícios do exercício físico','Praticar exercício físico regularmente traz uma série de benefícios que vão muito além da estética. O movimento do corpo contribui diretamente para a saúde do coração, melhora a circulação sanguínea e fortalece o sistema imunológico, ajudando a prevenir diversas doenças. Além disso, a atividade física auxilia no controle do peso corporal, aumenta a resistência muscular e melhora a flexibilidade, proporcionando mais disposição para as tarefas do dia a dia.\r\n\r\nOs efeitos positivos também aparecem na saúde mental. Durante a prática de exercícios, o corpo libera endorfinas, hormônios responsáveis pela sensação de bem-estar, o que ajuda a reduzir o estresse, a ansiedade e os sintomas de depressão. Com isso, as pessoas tendem a se sentir mais confiantes, motivadas e com melhor autoestima. A atividade física ainda contribui para a melhora da qualidade do sono, promovendo noites mais tranquilas e reparadoras.\r\n\r\nOutro benefício importante é o aumento da capacidade de concentração e da memória. Ao estimular a circulação de oxigênio no cérebro, o exercício favorece o desempenho cognitivo e a clareza mental. Dessa forma, torna-se um grande aliado tanto nos estudos quanto no trabalho.\r\n\r\nManter uma rotina de exercícios, mesmo que com atividades simples como caminhadas, alongamentos ou treinos leves, já faz diferença para a saúde física e emocional. O mais importante é escolher algo prazeroso e praticar com regularidade. Assim, o exercício deixa de ser apenas uma obrigação e passa a ser um hábito essencial para uma vida mais equilibrada e saudável.','2025-12-04 17:17:36','imgs/1765128603_transferir__4_.jpeg',4,'benefcios-do-exerccio-fsico',2),(5,'Final do mundial de clubes','A final do Mundial de Clubes 2025 foi disputada no dia 1 de junho e entrou para a história com uma atuação dominante do Chelsea, que venceu o Paris Saint-Germain por 3 a 0, garantindo de forma categórica o título mundial. Diante de um estádio lotado e de um cenário de grande expectativa, a equipa inglesa mostrou superioridade desde os primeiros minutos.\r\n\r\nO primeiro tempo foi praticamente todo controlado pelo Chelsea. O placar foi aberto aos 22 minutos, quando Cole Palmer recebeu dentro da área e finalizou com precisão para fazer 1 a 0. O mesmo jogador ampliou apenas oito minutos depois, aos 30’, aproveitando uma jogada rápida pelo meio e batendo com classe para marcar o segundo gol da final. O PSG, visivelmente abalado, tentou reagir, mas encontrou dificuldades para furar a defesa inglesa.\r\n\r\nAinda antes do intervalo, o Chelsea voltou a marcar. Aos 43 minutos, João Pedro aproveitou um cruzamento na área e finalizou de primeira, fazendo o 3 a 0 e praticamente definindo a decisão ainda na etapa inicial.\r\n\r\nNo segundo tempo, o PSG tentou equilibrar as ações, mas sem grande efetividade ofensiva. O Chelsea manteve o controle da partida, administrando a vantagem com segurança e inteligência tática. O momento mais tenso do jogo aconteceu já na reta final, aos 85 minutos, quando João Neves foi expulso após uma agressão sobre Cucurella, deixando a equipa francesa com um jogador a menos nos minutos finais.\r\n\r\nCom o apito final, a festa tomou conta dos jogadores e torcedores do Chelsea, que celebraram a conquista do Mundial de Clubes 2025 com uma das vitórias mais marcantes já registradas em finais do torneio. A atuação coletiva, aliada ao brilho individual de Cole Palmer, foi decisiva para um título conquistado de forma incontestável.','2025-12-07 17:44:04','imgs/1765301668_final_mundial_clubes.jpg',2,'final-do-mundial-de-clubes',3),(7,'Novo filme Português chega aos cinemas','“Pátio da Saudade” chega às salas de cinema como uma daquelas obras que não precisam de grandes efeitos para agarrar o público — basta-lhes verdade, silêncio e personagens que parecem viver mesmo ao nosso lado. O filme acompanha a vida num antigo pátio lisboeta onde famílias, vizinhos e memórias se cruzam diariamente, revelando pequenos dramas e afetos que dão forma ao quotidiano. A narrativa gira em torno de Mariana, uma jovem que regressa ao pátio depois de anos fora, confrontando-se com o passado que deixou para trás e com as mudanças inevitáveis do tempo. À medida que reencontra velhos conhecidos, descobre que cada esquina guarda uma história — algumas doces, outras duras, todas profundamente humanas. “Pátio da Saudade” é, no fundo, um retrato sensível da identidade portuguesa: o convívio, a nostalgia, as rotinas partilhadas e a força das relações que resistem. Com uma realização discreta mas segura, e interpretações que soam incrivelmente naturais, o filme convida o espectador a observar devagar, a lembrar e a reconhecer-se naqueles momentos simples que, muitas vezes, dizem mais do que palavras grandes. É um filme sobre a passagem do tempo, sobre o que se perde e o que se guarda, e sobre como, mesmo quando partimos, há sempre um lugar que nos chama de volta.','2025-12-11 16:39:27','imgs/1765471167_cartaz138959_grande-e1755619883960-739x470.jpg',6,'novo-filme-portugus-chega-aos-cinemas',2),(8,'Tendências Económicas Globais em 2025: Fatores Que Estão a Moldar o Mercado','A economia global em 2025 é marcada por tendências observadas e documentadas por instituições internacionais, como o aumento contínuo da digitalização, a expansão da inteligência artificial no setor produtivo e a transição energética em curso em vários países. As economias desenvolvidas mantêm níveis elevados de integração tecnológica, com investimentos constantes em automação e infraestruturas digitais, enquanto várias economias emergentes continuam a expandir a sua capacidade industrial e a reforçar o comércio regional. A inflação tem sido um ponto de atenção recorrente nos últimos anos, e muitos bancos centrais continuam a ajustar políticas monetárias com o objetivo de manter a estabilidade de preços. No comércio internacional, a circulação de bens e serviços permanece influenciada por cadeias de abastecimento mais diversificadas e pela reorganização de rotas logísticas iniciada após perturbações registadas nos anos anteriores. A nível energético, vários países aumentam gradualmente a quota de produção proveniente de fontes renováveis, acompanhando metas definidas em acordos internacionais. Estes fatores, combinados, moldam o comportamento dos mercados, influenciam decisões de investimento e determinam o ritmo de crescimento económico a médio prazo.','2025-12-11 16:52:21','imgs/1765471941_transferir.jpg',7,'tendncias-econmicas-globais-em-2025-fatores-que-esto-a-moldar-o-mercado',4);
/*!40000 ALTER TABLE `artigos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `categoria` varchar(100) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `categoria` (`categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias`
--

LOCK TABLES `categorias` WRITE;
/*!40000 ALTER TABLE `categorias` DISABLE KEYS */;
INSERT INTO `categorias` VALUES (6,'Arte'),(1,'Ciência e Tecnologia'),(2,'Desporto'),(7,'Economia'),(3,'Educação'),(4,'Saúde');
/*!40000 ALTER TABLE `categorias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comentarios` (
  `id_comentario` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `comentario` text NOT NULL,
  `data` datetime DEFAULT CURRENT_TIMESTAMP,
  `id_artigo` int NOT NULL,
  PRIMARY KEY (`id_comentario`),
  KEY `fk_comentarios_user` (`id_user`),
  KEY `fk_comentarios_artigo` (`id_artigo`),
  CONSTRAINT `fk_comentarios_artigo` FOREIGN KEY (`id_artigo`) REFERENCES `artigos` (`id_artigo`),
  CONSTRAINT `fk_comentarios_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios`
--

LOCK TABLES `comentarios` WRITE;
/*!40000 ALTER TABLE `comentarios` DISABLE KEYS */;
INSERT INTO `comentarios` VALUES (8,2,'Excelente artigo sobre IA!','2025-12-04 17:18:49',1),(9,3,'Muito interessante, gostei bastante.','2025-12-04 17:18:49',1),(10,4,'Esse jogo promete!','2025-12-04 17:18:49',2),(11,2,'Mal posso esperar pela final.','2025-12-04 17:18:49',2),(12,3,'Ótimas dicas, vou aplicar algumas.','2025-12-04 17:18:49',3),(13,4,'A saúde deve ser sempre prioridade.','2025-12-04 17:18:49',4),(14,2,'Concordo totalmente!','2025-12-04 17:18:49',4),(17,7,'Que jogo incrível!!!!','2025-12-07 15:57:33',2),(18,7,'uau','2025-12-07 16:03:42',2),(19,4,'Que inesperado!!','2025-12-11 16:21:52',5);
/*!40000 ALTER TABLE `comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `likes`
--

DROP TABLE IF EXISTS `likes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `likes` (
  `id_like` int NOT NULL AUTO_INCREMENT,
  `id_user` int NOT NULL,
  `id_artigo` int NOT NULL,
  PRIMARY KEY (`id_like`),
  UNIQUE KEY `id_user` (`id_user`,`id_artigo`),
  KEY `fk_likes_artigo` (`id_artigo`),
  CONSTRAINT `fk_likes_artigo` FOREIGN KEY (`id_artigo`) REFERENCES `artigos` (`id_artigo`),
  CONSTRAINT `fk_likes_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `likes`
--

LOCK TABLES `likes` WRITE;
/*!40000 ALTER TABLE `likes` DISABLE KEYS */;
INSERT INTO `likes` VALUES (1,2,1),(3,2,2),(6,2,3),(2,3,1),(5,3,3),(8,3,4),(4,4,2),(7,4,4),(13,6,1),(14,6,5);
/*!40000 ALTER TABLE `likes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `salt` varchar(255) NOT NULL,
  `superuser` tinyint(1) DEFAULT '0',
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `biografia` text,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'joao','joao@email.com','João','Silva','joao.jpg','$2y$10$VLtvI2We107CesXjc0oeuu/o95xBRRH3Jc3DmBnyi035kJnUBnULi','',0,'imgs/avatars/avatar_user_2_1765302183.png','author','Chamo-me João Silva e cresci rodeado por histórias, tecnologia e uma curiosidade constante sobre o que move o mundo. Desde cedo descobri que escrever era a forma mais natural de transformar essa curiosidade em algo concreto. Ao longo dos anos, acabei por encontrar na comunicação o meu espaço: um ponto de encontro entre criatividade, análise e paixão.\r\n\r\nA minha trajetória como autor tem sido marcada pela diversidade dos temas que me fascinam. Do cinema português, que continua a surpreender pela autenticidade e coragem das narrativas, à evolução da inteligência artificial dentro da indústria, onde observo com entusiasmo (e algum sentido crítico) as mudanças que moldam o nosso futuro. Ao mesmo tempo, mantenho um interesse profundo pelo bem-estar e pela saúde, o que me levou a explorar os benefícios do exercício físico e o impacto que pequenas escolhas diárias podem ter na vida das pessoas.\r\n\r\nEscrever, para mim, é mais do que informar: é ligar mundos distintos, criar pontes e despertar reflexões. Cada artigo que publico é um fragmento do meu percurso — e continuo curioso para descobrir os próximos temas que vão cruzar o meu caminho.'),(3,'maria','maria@email.com','Maria','Santos','maria.jpg','$2y$10$aiKlbFzf6oevRhqjYHsQCOHyeJRcdbrdgVWyJ0D8po5s3713UnyL2','',1,'imgs/avatars/avatar_user_3_1765126203.jpg','author','Chamo-me Maria Santos e, desde que me lembro, o futebol faz parte da minha vida. Cresci entre relatos de jogos, discussões acesas sobre táticas e a emoção pura que só um estádio cheio consegue oferecer. Quando comecei a escrever, percebi que era no desporto, especialmente no futebol que realmente encontrava a minha voz.\r\n\r\nAo longo da minha trajetória como autora, tenho-me dedicado a analisar partidas, destacar talentos emergentes e explorar os bastidores que muitas vezes passam despercebidos ao público. Para mim, o futebol é mais do que um jogo: é cultura, é identidade, é emoção coletiva. Cada texto que escrevo procura capturar essa essência, seja ao comentar uma jogada brilhante, seja ao refletir sobre o impacto social do desporto.\r\n\r\nEscrever sobre futebol permite-me unir paixão e rigor, emoção e análise. E enquanto houver uma bola a rolar, continuarei a contar as histórias que ela inspira.'),(4,'ana','ana@email.com','Ana','Costa','ana.jpg','$2y$10$pumfKf8QdyXq3DxMg2ptrulDtMQJzk9QBeM5csiwMugeYxBvolWOO','',0,'imgs/avatars/avatar_user_4_1765301815.png','author','Chamo-me Ana Costa e sempre fui movida por duas grandes curiosidades: compreender o mundo à minha volta e encontrar formas de aprender melhor todos os dias. Ao longo do meu percurso, descobri na escrita uma forma de transformar essas inquietações em conhecimento útil para os outros.\r\n\r\nO meu interesse pela economia levou-me a explorar as tendências globais e os fatores que moldam o mercado, especialmente num tempo em que tudo evolui tão depressa. Gosto de analisar cenários, identificar padrões e traduzir temas complexos em ideias claras e acessíveis. Ao mesmo tempo, mantenho uma paixão por métodos de estudo e desenvolvimento pessoal, talvez porque sempre acreditei que aprender bem é uma ferramenta poderosa para qualquer área da vida.\r\n\r\nOs meus textos refletem essa dualidade: por um lado, o rigor analítico das grandes questões económicas; por outro, a proximidade prática das dicas para estudar melhor. Escrever permite-me conciliar estas duas dimensões e partilhar conhecimento de forma simples, objetiva e inspiradora. Continuo motivada pela vontade de ajudar leitores a compreender o mundo e a crescer dentro dele.'),(6,'dev','dev@local','Dev','User',NULL,'$2y$10$KFhuby1mN/CTyUa7.m1Wi.UN8NEYBABa10nLA80dA0GlSEuopxUlq','',1,'imgs/avatars/avatar_user_6_1765301945.jpg','admin',NULL),(7,'vasco','vasco@email.com','vasco','coelho',NULL,'$2y$10$QpMVhlMfuf/2n3ifL/1sr.a256/RZC2QF0jbXjX/dFjbgJsBerQGq','',0,'imgs/avatars/avatar_user_7_1765302043.jpg','moderator',NULL),(8,'salvador','salvador@email.com','jose','cabeças',NULL,'$2y$10$gqKk6KFQkF0xzlLQh75Q8eKhN3VR0hGTlAjwdsBMeSuZ5V30dk/mq','',0,'imgs/avatars/avatar_user_8_1765302073.jpg','admin',NULL),(9,'user','user@email','user','user',NULL,'$2y$10$wXIOEb2P7Umq2ihuX5pRb..wb9AB/UYv3nQ2.wvh3JbybTASdNe5C','',0,NULL,'user',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-11 23:17:51
