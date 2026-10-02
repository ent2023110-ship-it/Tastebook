-- =======================================================
-- TasteBook – Digital Recipe Book Database
-- University Project: ICT 2209 - Web Technologies
-- Rajarata University of Sri Lanka
-- =======================================================

-- Create Database if not exists
CREATE DATABASE IF NOT EXISTS `tastebook` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tastebook`;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `recipes`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `recipes`
-- --------------------------------------------------------
CREATE TABLE `recipes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `ingredients` TEXT NOT NULL,
  `instructions` TEXT NOT NULL,
  `image` VARCHAR(255) DEFAULT 'default_recipe.jpg',
  `category` VARCHAR(40) NOT NULL DEFAULT 'Dinner',
  `prep_time` SMALLINT UNSIGNED NOT NULL DEFAULT 15,
  `cook_time` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `servings` SMALLINT UNSIGNED NOT NULL DEFAULT 4,
  `difficulty` ENUM('Easy', 'Medium', 'Hard') NOT NULL DEFAULT 'Easy',
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  KEY `idx_recipes_category` (`category`),
  KEY `idx_recipes_created_at` (`created_at`),
  CONSTRAINT `fk_recipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `favorites`
-- --------------------------------------------------------
CREATE TABLE `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `recipe_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_favorites_user_recipe` (`user_id`, `recipe_id`),
  KEY `idx_favorites_recipe` (`recipe_id`),
  CONSTRAINT `fk_favorites_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_favorites_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Dumping sample data for table `users`
-- Default test password for all sample users: "password123"
-- Generated with PHP password_hash('password123', PASSWORD_BCRYPT)
-- --------------------------------------------------------
INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Chef Gordon', 'gordon@tastebook.com', '$2y$10$Vr7YqyzE4o4lBHzClHCs.uv/1bmf8ySnz0bs1FShjRlf.rk1qt10u', NOW()),
(2, 'Sarah Jenkins', 'sarah@tastebook.com', '$2y$10$Vr7YqyzE4o4lBHzClHCs.uv/1bmf8ySnz0bs1FShjRlf.rk1qt10u', NOW()),
(3, 'Kasun Perera', 'kasun@tastebook.com', '$2y$10$Vr7YqyzE4o4lBHzClHCs.uv/1bmf8ySnz0bs1FShjRlf.rk1qt10u', NOW());

-- --------------------------------------------------------
-- Dumping sample data for table `recipes`
-- --------------------------------------------------------
INSERT INTO `recipes` (`id`, `title`, `description`, `ingredients`, `instructions`, `image`, `user_id`, `created_at`) VALUES
(1, 'Classic Chicken BBQ Pizza', 
'A gourmet homemade pizza loaded with grilled BBQ chicken breast, melted mozzarella cheese, red onions, and fresh cilantro on a crispy golden crust.',
'1 premade pizza dough or fresh dough base
200g cooked chicken breast, shredded
1/2 cup smoky barbecue sauce
1 1/2 cups shredded mozzarella cheese
1/2 small red onion, thinly sliced
1 tbsp olive oil
Fresh cilantro leaves for garnish
1 tsp Italian dried herbs',
'1. Preheat your oven to 220°C (425°F) and lightly grease a pizza baking tray.
2. Roll out the pizza dough onto a floured surface to your desired thickness and transfer to the baking tray.
3. In a small bowl, toss the shredded chicken with 2 tablespoons of BBQ sauce.
4. Spread the remaining BBQ sauce evenly across the dough base leaving a 1/2 inch crust border.
5. Sprinkle half of the mozzarella cheese, arrange the BBQ chicken and sliced red onions, then top with the remaining cheese and dried herbs.
6. Bake for 12-15 minutes until the cheese is bubbling with golden spots and crust is crisp.
7. Garnish with fresh cilantro, slice, and serve hot.',
'chicken_pizza.jpg', 1, NOW()),

(2, 'Creamy Garlic Chicken Alfredo Pasta', 
'Velvety fettuccine tossed in a rich homemade parmesan garlic cream sauce, topped with seasoned pan-seared golden chicken strips and fresh parsley.',
'250g fettuccine pasta
2 boneless chicken breasts, sliced into strips
2 tbsp butter
1 tbsp olive oil
3 cloves garlic, finely minced
1 cup heavy cream
1 cup freshly grated Parmesan cheese
Salt and freshly cracked black pepper to taste
Fresh parsley, chopped for garnish',
'1. Cook fettuccine in a large pot of salted boiling water according to package instructions until al dente. Reserve 1/2 cup pasta water, then drain.
2. Season chicken strips with salt, black pepper, and Italian seasoning.
3. Heat olive oil and 1 tbsp butter in a large skillet over medium-high heat. Sear chicken strips for 6-8 minutes until golden brown and cooked through. Remove and set aside.
4. In the same skillet, melt the remaining 1 tbsp butter and sauté minced garlic for 1 minute until fragrant.
5. Pour in heavy cream and bring to a gentle simmer for 2-3 minutes.
6. Reduce heat to low, stir in grated Parmesan cheese until melted and silky smooth.
7. Toss the cooked pasta into the creamy sauce, adding a splash of reserved pasta water if needed.
8. Plate pasta, arrange chicken strips on top, garnish with cracked black pepper and fresh parsley.',
'chicken_pasta.jpg', 2, NOW()),

(3, 'Decadent Molten Chocolate Fudge Cake', 
'Ultra-moist, rich double chocolate sponge layered with silky chocolate ganache and finished with a glossy glaze for true dessert lovers.',
'2 cups all-purpose flour
2 cups granulated sugar
3/4 cup unsweetened cocoa powder
2 tsp baking powder
1 1/2 tsp baking soda
1 tsp salt
2 large eggs
1 cup whole milk
1/2 cup vegetable oil
2 tsp vanilla extract
1 cup hot brewed coffee or boiling water
For Frosting: 200g dark chocolate melted with 1 cup heavy cream',
'1. Preheat oven to 175°C (350°F). Grease and flour two 9-inch round cake pans.
2. In a large mixing bowl, sift together flour, sugar, cocoa powder, baking powder, baking soda, and salt.
3. Add eggs, milk, oil, and vanilla extract. Beat with a mixer on medium speed for 2 minutes until smooth.
4. Stir in hot coffee/boiling water by hand (the batter will be thin, which creates the moist texture).
5. Pour evenly into prepared pans and bake for 30-35 minutes or until a toothpick inserted into the center comes out clean.
6. Allow cakes to cool in pans for 10 minutes, then turn out onto wire racks to cool completely.
7. Prepare ganache frosting by heating cream and pouring over chopped dark chocolate, stirring until glossy.
8. Layer cakes with ganache frosting and spread over the top and sides.',
'chocolate_cake.jpg', 1, NOW()),

(4, 'Special Sri Lankan Vegetable & Egg Fried Rice', 
'A fragrant, colorful wok-tossed basmati rice packed with crisp garden vegetables, scrambled eggs, aromatic spices, and a touch of sesame soy sauce.',
'3 cups cooked Basmati rice (chilled overnight)
2 tbsp vegetable oil
1 tsp sesame oil
2 eggs, lightly beaten
1 cup shredded carrots
1 cup finely sliced leeks or spring onions
1/2 cup diced cabbage
2 cloves garlic, minced
1 tsp ginger paste
2 tbsp soy sauce
1 tsp chili flakes
Salt and crushed black pepper to taste',
'1. Heat 1 tbsp oil in a large wok or skillet over high heat. Pour in beaten eggs, scramble quickly until soft, and remove to a plate.
2. Add remaining vegetable oil and sesame oil to the wok. Add minced garlic and ginger paste, stir-frying for 30 seconds.
3. Add carrots and cabbage, stir-frying rapidly for 2 minutes on high heat to keep vegetables crunchy.
4. Add the cold cooked rice, breaking up any clumps with a spatula.
5. Drizzle soy sauce, sprinkle chili flakes, salt, and black pepper. Toss everything continuously for 3 minutes over high heat.
6. Fold in the scrambled eggs and sliced leeks/spring onions.
7. Cook for one final minute until piping hot and serve with chili paste or gravy.',
'fried_rice.jpg', 3, NOW()),

(5, 'Juicy Gourmet Beef & Cheese Burger', 
'A thick and succulent seasoned ground beef patty topped with melted cheddar, crisp lettuce, ripe tomatoes, caramelized onions, and secret burger sauce on toasted brioche.',
'400g ground beef (80/20 blend)
2 soft brioche burger buns
2 slices aged cheddar cheese
1 ripe tomato, sliced
4 crisp lettuce leaves
1 small onion, sliced and caramelized
2 tbsp burger sauce (mayo, ketchup, relish, mustard)
1 tbsp butter for toasting buns
Salt and freshly ground black pepper',
'1. Divide ground beef into two equal patties, making a slight thumb indentation in the center of each. Season generously with salt and black pepper on both sides.
2. Heat a cast-iron skillet or grill over medium-high heat until smoking hot.
3. Place patties on the grill and sear without moving for 3-4 minutes until a deep crust forms.
4. Flip patties and immediately place a slice of cheddar cheese on each. Cover pan with a lid for 2 minutes to melt cheese.
5. In another pan, melt butter and toast brioche bun halves cut-side down until golden.
6. Spread burger sauce on bottom bun, add lettuce and sliced tomatoes.
7. Place the juicy cheesy beef patty on top, crown with caramelized onions, and close with top bun.',
'burger.jpg', 2, NOW()),

(6, 'Fluffy Golden Buttermilk Pancakes', 
'Cloud-soft, light-as-air buttermilk pancakes served with melted butter, pure maple syrup, and fresh berries for the ultimate breakfast treat.',
'2 cups all-purpose flour
2 tbsp granulated sugar
2 tsp baking powder
1/2 tsp baking soda
1/2 tsp salt
1 3/4 cups buttermilk (or milk + 1 tbsp lemon juice)
2 large eggs
4 tbsp unsalted butter, melted and cooled
1 tsp pure vanilla extract
Butter or oil for griddle
Maple syrup and fresh berries for serving',
'1. In a large bowl, whisk together flour, sugar, baking powder, baking soda, and salt.
2. In a separate bowl, whisk buttermilk, eggs, melted butter, and vanilla extract.
3. Pour wet ingredients into dry ingredients and gently fold with a spatula until just combined (do not overmix; small lumps are okay). Let batter rest for 5 minutes.
4. Heat a non-stick griddle or skillet over medium-low heat and lightly brush with butter.
5. Pour 1/4 cup of batter for each pancake onto the griddle.
6. Cook until bubbles appear on the surface and edges look set (about 2-3 minutes).
7. Flip carefully and cook for an additional 1-2 minutes until golden brown on the other side.
8. Stack pancakes warm, top with butter, drizzle with maple syrup, and scatter fresh berries.',
'pancakes.jpg', 1, NOW());

UPDATE `recipes` SET `category` = 'Dinner', `prep_time` = 20, `cook_time` = 25, `servings` = 4, `difficulty` = 'Medium' WHERE `id` = 1;
UPDATE `recipes` SET `category` = 'Dinner', `prep_time` = 15, `cook_time` = 25, `servings` = 4, `difficulty` = 'Medium' WHERE `id` = 2;
UPDATE `recipes` SET `category` = 'Desserts', `prep_time` = 30, `cook_time` = 35, `servings` = 8, `difficulty` = 'Medium' WHERE `id` = 3;
UPDATE `recipes` SET `category` = 'Vegetarian', `prep_time` = 20, `cook_time` = 15, `servings` = 4, `difficulty` = 'Easy' WHERE `id` = 4;
UPDATE `recipes` SET `category` = 'Lunch', `prep_time` = 20, `cook_time` = 15, `servings` = 2, `difficulty` = 'Medium' WHERE `id` = 5;
UPDATE `recipes` SET `category` = 'Breakfast', `prep_time` = 10, `cook_time` = 15, `servings` = 4, `difficulty` = 'Easy' WHERE `id` = 6;

INSERT INTO `favorites` (`user_id`, `recipe_id`) VALUES
(1, 2),
(2, 1),
(3, 6);

-- --------------------------------------------------------
-- Dumping sample data for table `messages`
-- --------------------------------------------------------
INSERT INTO `messages` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Nimal Silva', 'nimal@example.com', 'TasteBook is an incredible recipe platform! The UI is very clean and easy to use.', NOW()),
(2, 'Amara Weerasinghe', 'amara@example.com', 'Loved the Garlic Chicken Pasta recipe. Turned out delicious. Keep up the great work!', NOW());
