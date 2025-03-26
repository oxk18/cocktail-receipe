<?php
include_once('./_common.php');
if (!defined('_GNUBOARD_')) exit; // 그누보드 관련 필수 파일 포함

$g5['title'] = 'Cocktail Recipe Search';
include_once(G5_PATH.'/head.php');
?>

<div class="cocktail-search-container">
    <form method="GET" class="cocktail-search-form">
            <select name="search_type">
                <option value="name">칵테일 이름으로 검색 (e.g. mojito)</option>                
                <option value="ingredient">재료로 검색 (e.g. vodka)</option>
                <option value="alcoholic">Non Alcoholic 검색</option>
            </select>

        <input type="text" name="cocktail_search" placeholder="Submit your search in english" 
            value="<?php echo isset($_GET['cocktail_search']) ? htmlspecialchars($_GET['cocktail_search']) : ''; ?>">
        <input type="submit" value="검색" class="btn_submit">
    </form>

    <?php
    if (isset($_GET['cocktail_search']) && !empty($_GET['cocktail_search'])) {
        $search_query = urlencode($_GET['cocktail_search']);

        $search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'name';
            
            switch($search_type) {
                case 'ingredient':
                    $api_url = "https://www.thecocktaildb.com/api/json/v1/1/filter.php?i={$search_query}";
                    break;
                case 'alcoholic':
                    $api_url = "https://www.thecocktaildb.com/api/json/v1/1/filter.php?a=Non_Alcoholic";
                    break;  
                default:
                    $api_url = "https://www.thecocktaildb.com/api/json/v1/1/search.php?s={$search_query}";
            }




        // API 요청
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($data && isset($data['drinks']) && is_array($data['drinks'])) {
            echo '<div class="cocktail-results">';
            foreach ($data['drinks'] as $drink) {

                // For ingredient and non_alcoholic searches, we need to fetch full meal details
                if ($search_type === 'ingredient' || $search_type === 'alcoholic') {
                    $cocktail_id = $drink['idDrink'];
                    $detail_url = "https://www.thecocktaildb.com/api/json/v1/1/lookup.php?i=" . $cocktail_id;
                    
                    $ch_detail = curl_init();
                    curl_setopt($ch_detail, CURLOPT_URL, $detail_url);
                    curl_setopt($ch_detail, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_detail, CURLOPT_SSL_VERIFYPEER, false);
                    $detail_response = curl_exec($ch_detail);
                    curl_close($ch_detail);
                    
                    $detail_data = json_decode($detail_response, true);
                    if ($detail_data && isset($detail_data['drinks'][0])) {
                        $drink = $detail_data['drinks'][0];
                    }
                }


                ?>
                <div class="cocktail-item">
                    <div class="cocktail-image">
                        <img src="<?php echo $drink['strDrinkThumb']; ?>" alt="<?php echo htmlspecialchars($drink['strDrink']); ?>">
                    </div>
                    <div class="cocktail-info">
                        <h3><?php echo htmlspecialchars($drink['strDrink']); ?></h3>
                        <p><strong>카테고리:</strong> <?php echo htmlspecialchars($drink['strCategory']); ?></p>
                        <p><strong>글래스 타입:</strong> <?php echo htmlspecialchars($drink['strGlass']); ?></p>
                        <p><strong>만드는 방법:</strong><br><?php echo nl2br(htmlspecialchars($drink['strInstructions'])); ?></p>
                        
                        <div class="ingredients">
                            <strong>재료:</strong>
                            <ul>
                            <?php
                            for ($i = 1; $i <= 15; $i++) {
                                $ingredient = $drink["strIngredient{$i}"];
                                $measure = $drink["strMeasure{$i}"];
                                if ($ingredient) {
                                    echo "<li>" . htmlspecialchars($ingredient);
                                    if ($measure) {
                                        echo " - " . htmlspecialchars($measure);
                                    }
                                    echo "</li>";
                                }
                            }
                            ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        } else {
            echo '<p class="empty-result">검색 결과가 없습니다.</p>';
        }
    }
    ?>
</div>

<style>
.cocktail-search-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;    
}
/*
.cocktail-search-form {
    margin-bottom: 30px;
    text-align: center;
}

.cocktail-search-form input[type="text"] {
    width: 300px;
    padding: 8px;
    margin-right: 10px;
}
*/

.cocktail-search-form {
    margin: 40px auto;
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    max-width: 800px;
}

.cocktail-search-form select {
    width: 300px;
    padding: 12px;
    /*margin-bottom: 15px;*/
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cocktail-search-form select:hover {
    border-color: #4a90e2;
}

.cocktail-search-form select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

.cocktail-search-form input[type="text"] {
    width: 300px;
    padding: 12px;
    margin: 0 10px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.cocktail-search-form input[type="text"]:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

.cocktail-search-form .btn_submit {
    padding: 12px 30px;
    background: linear-gradient(45deg, #4a90e2, #63b3ed);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
}

.cocktail-search-form .btn_submit:hover {
    background: linear-gradient(45deg, #357abd, #4a90e2);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.cocktail-search-form .btn_submit:active {
    transform: translateY(0);
}



.cocktail-item {
    display: flex;    
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    /*color: rgb(255, 255, 255);*/
    color:black;
    background: #111;    
    position: relative;    
    z-index: 0;
    box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}

.cocktail-item:before {
  content: "";
  background: linear-gradient(
    45deg,
    #ff0000,
    #ff7300,
    #fffb00,
    #48ff00,
    #00ffd5,
    #002bff,
    #7a00ff,
    #ff00c8,
    #ff0000
  );
  position: absolute;
  top: -2px;
  left: -2px;
  background-size: 400%;
  z-index: -1;
  filter: blur(5px);
  -webkit-filter: blur(5px);
  width: calc(100% + 4px);
  height: calc(100% + 4px);
  animation: glowing-cocktail-item 20s linear infinite;
  transition: opacity 0.3s ease-in-out;
  border-radius: 10px;
}

@keyframes glowing-cocktail-item {
  0% {
    background-position: 0 0;
  }
  50% {
    background-position: 400% 0;
  }
  100% {
    background-position: 0 0;
  }
}

.cocktail-item:after {
  z-index: -1;
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  /*background: #222;*/
  background: white;
  left: 0;
  top: 0;
  border-radius: 10px;
}

.cocktail-item:hover { transform: scale(1.05); -webkit-transform: scale(1.05);}

.cocktail-image {
    flex: 0 0 200px;
    margin-right: 20px;
}

.cocktail-image img {
    width: 100%;
    border-radius: 5px;
}

.cocktail-info {
    flex: 1;      
}

.cocktail-info h3 {
    margin-top: 0;
    color: #333;
}

.ingredients ul {
    list-style: none;
    padding-left: 0;
}

.ingredients li {
    margin-bottom: 5px;
}

.empty-result {
    text-align: center;
    color: #666;
    padding: 20px;
}

/* 모바일 반응형 스타일 추가 */
@media screen and (max-width: 768px) {
    .cocktail-item {
        flex-direction: column;
    }
    
    .cocktail-image {
        flex: none;
        width: 100%;
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .cocktail-info {
        width: 100%;
    }
  /*  
    .cocktail-search-form input[type="text"] {
        width: 100%;
        margin-bottom: 10px;
    }
  */
    .cocktail-search-form {
        padding: 20px;
    }

    .cocktail-search-form select,
    .cocktail-search-form input[type="text"] {
        width: 100%;
        margin: 10px 0;
    }

    .cocktail-search-form .btn_submit {
        width: 100%;
        margin-top: 10px;
    }  


}


</style>

<?php
include_once(G5_PATH.'/tail.php');
?>