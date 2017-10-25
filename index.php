<?php
/**
 * User: suji.zhao
 * Email: zhaosuji@foxmail.com
 * Date: 2017/10/19 9:38
 */
//动态规划
//一个10阶楼梯 每次只能走1步或者2步,走到头有几种走法
//F(10) = F(9) + F(8)最优子结构
//F(1),F(2) 边界
//F(n) = F(n-1) + F(n-2) 状态转移方程

function test1($n){
    if($n<1){
        return 0;
    }
    if($n==1){
        return 1;
    }
    if ($n==2){
        return 2;
    }
    return test1($n-1) + test1($n-2);
}

echo test1(4);
