package main

import (
	"testing"
	"time"

	"github.com/stretchr/testify/assert"
)

func TestGetRandomDuration(t *testing.T) {
	duration := GetRandomDuration(10)

	assert.IsType(t, time.Duration(0), duration)
	assert.True(t, int(duration) <= 10)
}
