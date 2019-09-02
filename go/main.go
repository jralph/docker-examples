package main

import (
	"fmt"
	"math/rand"
	"time"
)

func main() {
	for i := 0; true; i++ {
		fmt.Printf("[%s] Job '%d' complete.\n", time.Now(), i)
		time.Sleep(GetRandomDuration(5000) * time.Millisecond)
	}
}

// GetRandomDuration returns a random duration from 0 to n.
func GetRandomDuration(n int) time.Duration {
	rand.Seed(time.Now().UnixNano())
	x := rand.Intn(n)

	return time.Duration(x)
}
